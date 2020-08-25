<?php

declare(strict_types=1);

namespace App\Manager;

use App\Contract\Request\BroadcastListener\RoomAvailabilityRequest;
use App\Entity\Component;
use App\Entity\RoomAvailability;
use App\Exception\Manager\RoomAvailability\InvalidRoomStockTypeException;
use App\Exception\Manager\RoomAvailability\OutdatedRoomAvailabilityInformationException;
use App\Repository\ComponentRepository;
use App\Repository\RoomAvailabilityRepository;
use Psr\Log\LoggerInterface;

class RoomAvailabilityManager
{
    protected RoomAvailabilityRepository $repository;

    protected ComponentRepository $componentRepository;
    private LoggerInterface $logger;

    public function __construct(
        RoomAvailabilityRepository $repository,
        ComponentRepository $componentRepository,
        LoggerInterface $logger
    ) {
        $this->repository = $repository;
        $this->componentRepository = $componentRepository;
        $this->logger = $logger;
    }

    public function getRoomAvailabilitiesByMultipleComponentGoldenIds(
        array $componentGoldenIds,
        \DateTimeInterface $dateFrom,
        \DateTimeInterface $dateTo
    ): array {
        return $this->repository->findRoomAvailabilitiesByMultipleComponentGoldenIds($componentGoldenIds, $dateFrom, $dateTo);
    }

    public function getRoomAvailabilitiesByComponent(
        Component $component,
        \DateTimeInterface $dateFrom,
        \DateTimeInterface $dateTo
    ): array {
        return $this->repository->findRoomAvailabilitiesByComponent($component, $dateFrom, $dateTo);
    }

    /**
     * @throws \Exception
     */
    public function replace(RoomAvailabilityRequest $roomAvailabilityRequest): void
    {
        $datePeriod = $this->createDatePeriod($roomAvailabilityRequest);
        $component = $this->componentRepository->findOneByGoldenId($roomAvailabilityRequest->product->id);

        try {
            if (empty($component->roomStockType)) {
                throw new InvalidRoomStockTypeException();
            }

            $roomAvailabilityList = $this->repository->findByComponentAndDateRange(
                $component,
                $roomAvailabilityRequest->dateFrom,
                $roomAvailabilityRequest->dateTo
            );

            foreach ($datePeriod as $date) {
                $roomAvailability = $roomAvailabilityList[$date->format('Y-m-d')] ?? new RoomAvailability();
                if ($roomAvailability->externalUpdatedAt &&
                    $roomAvailabilityRequest->updatedAt < $roomAvailability->externalUpdatedAt) {
                    $this->logger->warning(OutdatedRoomAvailabilityInformationException::class, $roomAvailabilityRequest->getContext());
                    continue;
                }

                $roomAvailability->componentGoldenId = $roomAvailabilityRequest->product->id;
                $roomAvailability->component = $component;
                $roomAvailability->stock = $roomAvailabilityRequest->quantity;
                $roomAvailability->date = $date;
                $roomAvailability->isStopSale = $roomAvailabilityRequest->isStopSale;
                $roomAvailability->type = $component->roomStockType;
                $roomAvailability->externalUpdatedAt = $roomAvailabilityRequest->updatedAt ?? null;

                $this->repository->save($roomAvailability);
            }
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    private function createDatePeriod(RoomAvailabilityRequest $roomAvailabilityRequest): \DatePeriod
    {
        $beginDate = new \DateTime($roomAvailabilityRequest->dateFrom->format('Y-m-d'));
        $endDate = new \DateTime($roomAvailabilityRequest->dateTo->format('Y-m-d'));
        $endDate->modify('+1 day');
        $interval = new \DateInterval('P1D');

        return new \DatePeriod($beginDate, $interval, $endDate);
    }
}
