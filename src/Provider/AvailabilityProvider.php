<?php

declare(strict_types=1);

namespace App\Provider;

use App\Helper\AvailabilityHelper;
use App\Manager\ComponentManager;
use App\Manager\ExperienceManager;
use App\Manager\RoomAvailabilityManager;
use App\Manager\RoomPriceManager;
use App\Repository\BookingDateRepository;
use JMS\Serializer\ArrayTransformerInterface;
use JMS\Serializer\SerializerInterface;

class AvailabilityProvider
{
    protected SerializerInterface $serializer;
    private ArrayTransformerInterface $arraySerializer;
    protected ExperienceManager $experienceManager;
    protected ComponentManager $componentManager;
    protected RoomAvailabilityManager $roomAvailabilityManager;
    private RoomPriceManager $roomPriceManager;
    private BookingDateRepository $bookingDateRepository;
    protected AvailabilityHelper $availabilityHelper;

    public function __construct(
        SerializerInterface $serializer,
        ArrayTransformerInterface $arraySerializer,
        ExperienceManager $experienceManager,
        ComponentManager $componentManager,
        RoomAvailabilityManager $roomAvailabilityManager,
        RoomPriceManager $roomPriceManager,
        BookingDateRepository $bookingDateRepository,
        AvailabilityHelper $availabilityHelper
    ) {
        $this->serializer = $serializer;
        $this->arraySerializer = $arraySerializer;
        $this->experienceManager = $experienceManager;
        $this->componentManager = $componentManager;
        $this->roomAvailabilityManager = $roomAvailabilityManager;
        $this->roomPriceManager = $roomPriceManager;
        $this->bookingDateRepository = $bookingDateRepository;
        $this->availabilityHelper = $availabilityHelper;
    }

    public function getRoomAvailabilitiesByBoxIdAndStartDate(
        string $boxId,
        \DateTimeInterface $startDate
    ): array {
        return $this->roomAvailabilityManager->getRoomAvailabilitiesByBoxId($boxId, $startDate);
    }

    public function getRoomAndPricesAvailabilitiesByExperienceIdAndDates(
        string $experienceId,
        \DateTimeInterface $dateFrom,
        \DateTimeInterface $dateTo
    ): array {
        $roomAvailabilities = $this->roomAvailabilityManager->getRoomAndPriceAvailabilitiesByExperienceIdAndDates(
            $experienceId,
            $dateFrom,
            $dateTo
        );

        $bookingStockDate = $this->bookingDateRepository->findBookingDatesByExperiencesAndDates(
            [$experienceId],
            $dateFrom,
            $dateTo
        );

        return $this->availabilityHelper->getRealStock($roomAvailabilities, $bookingStockDate);
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

    public function getManageableComponentForGetPackage(string $experienceId): array
    {
        return $this->componentManager->getManageableComponentForGetPackage($experienceId);
    }
}
