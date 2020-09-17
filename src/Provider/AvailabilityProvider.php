<?php

declare(strict_types=1);

namespace App\Provider;

use App\CMHub\CMHub;
use App\Constraint\PartnerStatusConstraint;
use App\Contract\Response\CMHub\CMHubErrorResponse;
use App\Contract\Response\CMHub\CMHubResponse;
use App\Contract\Response\CMHub\GetAvailability\AvailabilityResponse;
use App\Contract\Response\CMHub\GetAvailabilityResponse;
use App\Entity\Component;
use App\Entity\Experience;
use App\Entity\ExperienceComponent;
use App\Entity\Partner;
use App\Helper\AvailabilityHelper;
use App\Manager\ComponentManager;
use App\Manager\ExperienceManager;
use App\Manager\RoomAvailabilityManager;
use App\Manager\RoomPriceManager;
use App\Repository\BookingDateRepository;
use JMS\Serializer\ArrayTransformerInterface;
use JMS\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;

class AvailabilityProvider
{
    private const DEFAULT_COMPONENT_DURATION = 1;

    protected CMHub $cmHub;
    protected SerializerInterface $serializer;
    private ArrayTransformerInterface $arraySerializer;
    protected ExperienceManager $experienceManager;
    protected ComponentManager $componentManager;
    protected RoomAvailabilityManager $roomAvailabilityManager;
    private RoomPriceManager $roomPriceManager;
    private BookingDateRepository $bookingDateRepository;

    public function __construct(
        CMHub $cmHub,
        SerializerInterface $serializer,
        ArrayTransformerInterface $arraySerializer,
        ExperienceManager $experienceManager,
        ComponentManager $componentManager,
        RoomAvailabilityManager $roomAvailabilityManager,
        RoomPriceManager $roomPriceManager,
        BookingDateRepository $bookingDateRepository
    ) {
        $this->cmHub = $cmHub;
        $this->serializer = $serializer;
        $this->arraySerializer = $arraySerializer;
        $this->experienceManager = $experienceManager;
        $this->componentManager = $componentManager;
        $this->roomAvailabilityManager = $roomAvailabilityManager;
        $this->roomPriceManager = $roomPriceManager;
        $this->bookingDateRepository = $bookingDateRepository;
    }

    public function getAvailability(
        int $productId,
        \DateTimeInterface $dateFrom,
        \DateTimeInterface $dateTo
    ): CMHubResponse {
        try {
            /** @psalm-suppress ArgumentTypeCoercion */
            $response = $this->serializer->deserialize(// @phpstan-ignore-line
                ($this->cmHub->getAvailability($productId, $dateFrom, $dateTo))->getContent(),
                sprintf('array<%s>', GetAvailabilityResponse::class),
                'json'
            );

            /** @psalm-suppress InvalidArgument $result */
            $result = new AvailabilityResponse($response); // @phpstan-ignore-line
        } catch (HttpExceptionInterface $exception) {
            $result = $this->arraySerializer->fromArray(
                $exception->getResponse()->toArray(false)['error'],
                CMHubErrorResponse::class
            );
        }

        return $result;
    }

    public function getRoomAvailabilitiesByBoxIdAndStartDate(
        string $boxId,
        \DateTimeInterface $startDate
    ): array {
        return $this->roomAvailabilityManager->getRoomAvailabilitiesByBoxId($boxId, $startDate);
    }

    public function getRoomAvailabilitiesByExperienceIdAndDates(
        string $experienceId,
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate
    ): array {
        return $this->roomAvailabilityManager->getRoomAvailabilitiesByExperienceId($experienceId, $startDate, $endDate);
    }

    public function getRoomAvailabilitiesByExperienceAndDates(
        Experience $experience,
        \DateTimeInterface $dateFrom,
        \DateTimeInterface $dateTo
    ): array {
        $partner = $experience->partner;

        /** @var ExperienceComponent $experienceComponent */
        $experienceComponent = $experience->experienceComponent->filter(
            static function ($experienceComponent) {
                return $experienceComponent->component->isReservable && $experienceComponent->isEnabled;
            }
        )->first();
        $componentDuration = $experienceComponent->component->duration ?? self::DEFAULT_COMPONENT_DURATION;

        if (false == $experienceComponent || PartnerStatusConstraint::PARTNER_STATUS_PARTNER !== $partner->status) {
            $roomAvailabilities = [];
            $componentGoldenId = '';
            $roomPrices = [];
            $componentSellable = false;
        } else {
            $roomAvailabilities = $this->getRoomAvailabilitiesAndFilterCeasePartnerByComponent(
                $experienceComponent->component,
                $partner,
                $dateFrom,
                $dateTo
            );

            $roomPrices = $this->roomPriceManager->getRoomPricesByComponentAndDateRange(
                $experienceComponent->component,
                $dateFrom,
                $dateTo
            );
            $componentGoldenId = $experienceComponent->componentGoldenId;
            $componentSellable = $experienceComponent->component->isSellable;
        }

        $roomAvailabilities = AvailabilityHelper::fillMissingAvailabilities(
            AvailabilityHelper::getRealStockByDate(
                $roomAvailabilities,
                $this->bookingDateRepository->findBookingDatesByComponentAndDate(
                    $componentGoldenId,
                    $dateFrom,
                    $dateTo
                )
            ),
            $componentGoldenId,
            $dateFrom,
            $dateTo
        );

        return [
            'duration' => $componentDuration,
            'isSellable' => $componentSellable,
            'availabilities' => $roomAvailabilities,
            'prices' => $roomPrices,
        ];
    }

    public function getRoomAvailabilitiesByExperienceIdsList(
        array $experienceIds,
        \DateTimeInterface $startDate
    ): array {
        $returnArray = [];
        $availabilities = $this->roomAvailabilityManager->getRoomAvailabilitiesByMultipleExperienceGoldenIds(
            $experienceIds,
            $startDate
        );

        $availabilities = AvailabilityHelper::getRealStock(
            $availabilities,
            $this->bookingDateRepository->findBookingDatesByExperiencesAndDate(
                $experienceIds,
                $startDate
            )
        );

        foreach ($availabilities as $availability) {
            $returnArray[$availability['experience_golden_id']] = [
                'duration' => $availability['duration'],
                'isSellable' => $availability['is_sellable'],
                'partnerId' => $availability['partner_golden_id'],
                'experienceId' => $availability['experience_golden_id'],
            ];
        }

        return $returnArray;
    }

    private function getRoomAvailabilitiesAndFilterCeasePartnerByComponent(
        Component $component,
        Partner $partner,
        \DateTimeInterface $dateFrom,
        \DateTimeInterface $dateTo
    ): array {
        $roomAvailabilities = $this->roomAvailabilityManager->getRoomAvailabilitiesByComponent(
            $component,
            $dateFrom,
            $dateTo
        );

        return $this->validatePartnerCeaseDate($roomAvailabilities, $partner, ($component->duration ?? self::DEFAULT_COMPONENT_DURATION));
    }

    private function validatePartnerCeaseDate(array $roomAvailabilities, Partner $partner, int $componentDuration): array
    {
        if (null !== $partner->ceaseDate) {
            $ceasedDatePlusDurationInterval = $partner->ceaseDate->sub(
                new \DateInterval('P'.$componentDuration.'D')
            );
            $ceasedDatePlusDurationDate = $ceasedDatePlusDurationInterval->format('Y-m-d');

            foreach ($roomAvailabilities as &$roomAvailability) {
                if ($ceasedDatePlusDurationDate <= $roomAvailability['date']->format('Y-m-d')) {
                    $roomAvailability['stock'] = 0;
                }
            }
        }

        return $roomAvailabilities;
    }
}
