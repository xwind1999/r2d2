<?php

declare(strict_types=1);

namespace App\Provider;

use App\CMHub\CMHub;
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
    private const STOCK_TYPE = 'stock';

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
        $dateDiff = $dateTo->diff($dateFrom)->days ?: 0;
        // DateFrom and DateTo is the stay date, not the checkout one
        $numberOfNights = $dateDiff + 1;
        $activeChannelExperienceIds = $this->experienceManager->filterListExperienceIdsByBoxId($boxId);
        $activeChannelComponents = $this->componentManager->getRoomsByExperienceGoldenIdsList(
            array_keys($activeChannelExperienceIds));
        $roomAvailabilities = $this->roomAvailabilityManager->getRoomAvailabilitiesByComponentGoldenIds(
            array_keys($activeChannelComponents), self::STOCK_TYPE, $dateFrom, $dateTo
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
        if (empty($experience->experienceComponent->first())) {
            return [];
        }

        $partner = $experience->partner;

        /** @var ExperienceComponent $experienceComponent */
        $experienceComponent = $experience->experienceComponent->first();
        $roomAvailabilities = $this->roomAvailabilityManager->getRoomAvailabilitiesListByComponentGoldenId(
            $experienceComponent->componentGoldenId,
            self::STOCK_TYPE,
            $dateFrom,
            $dateTo
        );
        $duration = $experienceComponent->component->duration ?: 1;

        return [
            'duration' => $duration,
            'isSellable' => $experienceComponent->component->isSellable,
            'availabilities' => LegacyAvailabilityProvider::PARTNER === $partner->status ?
                AvailabilityHelper::calculateAvailabilitiesByDuration($duration, $roomAvailabilities) : [],
        ];
    }
}
