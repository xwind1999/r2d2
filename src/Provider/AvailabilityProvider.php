<?php

declare(strict_types=1);

namespace App\Provider;

use App\CMHub\CMHub;
use App\Constraint\PartnerStatusConstraint;
use App\Contract\Response\CMHub\CMHubErrorResponse;
use App\Contract\Response\CMHub\CMHubResponse;
use App\Contract\Response\CMHub\GetAvailability\AvailabilityResponse;
use App\Contract\Response\CMHub\GetAvailabilityResponse;
use App\Entity\Experience;
use App\Entity\ExperienceComponent;
use App\Helper\AvailabilityHelper;
use App\Manager\ComponentManager;
use App\Manager\ExperienceManager;
use App\Manager\RoomAvailabilityManager;
use JMS\Serializer\ArrayTransformerInterface;
use JMS\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;

class AvailabilityProvider
{
    private const DEFAULT_COMPONENT_DURATION = 1;
    private const DEFAULT_DATE_DIFF_VALUE = 0;

    protected CMHub $cmHub;
    protected SerializerInterface $serializer;
    private ArrayTransformerInterface $arraySerializer;
    protected ExperienceManager $experienceManager;
    protected ComponentManager $componentManager;
    protected RoomAvailabilityManager $roomAvailabilityManager;

    public function __construct(
        CMHub $cmHub,
        SerializerInterface $serializer,
        ArrayTransformerInterface $arraySerializer,
        ExperienceManager $experienceManager,
        ComponentManager $componentManager,
        RoomAvailabilityManager $roomAvailabilityManager
    ) {
        $this->cmHub = $cmHub;
        $this->serializer = $serializer;
        $this->arraySerializer = $arraySerializer;
        $this->experienceManager = $experienceManager;
        $this->componentManager = $componentManager;
        $this->roomAvailabilityManager = $roomAvailabilityManager;
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

    public function getRoomAvailabilitiesByBoxIdAndDates(
        int $boxId,
        \DateTimeInterface $dateFrom,
        \DateTimeInterface $dateTo
    ): array {
        $dateDiff = $dateTo->diff($dateFrom)->days ?: self::DEFAULT_DATE_DIFF_VALUE;
        // DateFrom and DateTo is the stay date, not the checkout one
        $numberOfNights = $dateDiff + 1;
        $activeChannelExperienceIds = $this->experienceManager->filterListExperienceIdsByBoxId($boxId);
        $activeChannelComponents = $this->componentManager->getRoomsByExperienceGoldenIdsList(
            array_keys($activeChannelExperienceIds));
        $roomAvailabilities = $this->roomAvailabilityManager->getRoomAvailabilitiesByMultipleComponentGoldenIds(
            array_keys($activeChannelComponents), $dateFrom, $dateTo
        );
        $roomAvailabilities = AvailabilityHelper::mapRoomAvailabilitiesToExperience(
            $activeChannelComponents, $roomAvailabilities, $numberOfNights);

        return $roomAvailabilities;
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

        if (false == $experienceComponent || PartnerStatusConstraint::PARTNER_STATUS_PARTNER !== $partner->status) {
            $roomAvailabilities = [];
            $componentGoldenId = '';
            $componentSellable = false;
        } else {
            $roomAvailabilities = $this->roomAvailabilityManager->getRoomAvailabilitiesByComponentGoldenId(
                $experienceComponent->componentGoldenId,
                $dateFrom,
                $dateTo
            );
            $componentGoldenId = $experienceComponent->componentGoldenId;
            $componentSellable = $experienceComponent->component->isSellable;
        }

        $roomAvailabilities = AvailabilityHelper::fillMissingAvailabilities(
            $roomAvailabilities,
            $componentGoldenId,
            $dateFrom,
            $dateTo
        );

        $duration = $experienceComponent->component->duration ?? self::DEFAULT_COMPONENT_DURATION;

        return [
            'duration' => $duration,
            'isSellable' => $componentSellable,
            'availabilities' => AvailabilityHelper::convertToShortType($roomAvailabilities),
        ];
    }

    public function getRoomAvailabilitiesByExperienceIdsList(
        array $experienceIds,
        \DateTimeInterface $dateFrom,
        \DateTimeInterface $dateTo
    ): array {
        $returnArray = [];
        $experiencesWithLegitPartner = $this->experienceManager->filterIdsListWithPartnerStatus(
            $experienceIds,
            PartnerStatusConstraint::PARTNER_STATUS_PARTNER
        );
        $componentList = $this->componentManager->getRoomsByExperienceGoldenIdsList(
            array_keys($experiencesWithLegitPartner)
        );
        $roomAvailabilities = $this->roomAvailabilityManager->getRoomAvailabilitiesByMultipleComponentGoldenIds(
            array_keys($componentList),
            $dateFrom,
            $dateTo
        );

        $availabilitiesGroup = [];
        foreach ($roomAvailabilities as $availability) {
            $componentId = $availability['componentGoldenId'];
            $experienceId = $componentList[$componentId]['experienceGoldenId'];
            $availabilitiesGroup[$experienceId][] = $availability;
        }

        foreach ($availabilitiesGroup as $key => $item) {
            $componentId = $item[0]['componentGoldenId'];
            $item = AvailabilityHelper::fillMissingAvailabilities($item, $componentId, $dateFrom, $dateTo);
            $duration = $componentList[$componentId][0]['duration'] ?: self::DEFAULT_COMPONENT_DURATION;

            $returnArray[$key] = [
                'duration' => $duration,
                'isSellable' => $componentList[$componentId][0]['isSellable'],
                'partnerId' => $componentList[$componentId][0]['partnerGoldenId'],
                'availabilities' => AvailabilityHelper::convertToShortType($item),
            ];
        }

        return $returnArray;
    }
}
