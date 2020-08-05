<?php

declare(strict_types=1);

namespace App\Manager;

use App\Contract\Request\BroadcastListener\RoomAvailabilityRequest;
use App\Contract\Request\Internal\RoomAvailability\RoomAvailabilityCreateRequest;
use App\Contract\Request\Internal\RoomAvailability\RoomAvailabilityUpdateRequest;
use App\Entity\RoomAvailability;
use App\Exception\Manager\RoomAvailability\InvalidRoomStockTypeException;
use App\Exception\Manager\RoomAvailability\OutdatedRoomAvailabilityInformationException;
use App\Exception\Repository\EntityNotFoundException;
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

    public function create(RoomAvailabilityCreateRequest $roomAvailabilityCreateRequest): RoomAvailability
    {
        $component = $this->componentRepository->findOneByGoldenId($roomAvailabilityCreateRequest->componentGoldenId);

        $roomAvailability = new RoomAvailability();

        $roomAvailability->component = $component;
        $roomAvailability->componentGoldenId = $roomAvailabilityCreateRequest->componentGoldenId;
        $roomAvailability->stock = $roomAvailabilityCreateRequest->stock;
        $roomAvailability->date = $roomAvailabilityCreateRequest->date;
        $roomAvailability->type = $roomAvailabilityCreateRequest->type;

        $this->repository->save($roomAvailability);

        return $roomAvailability;
    }

    /**
     * @throws EntityNotFoundException
     */
    public function get(string $uuid): RoomAvailability
    {
        return $this->repository->findOne($uuid);
    }

    /**
     * @throws EntityNotFoundException
     */
    public function delete(string $uuid): void
    {
        $roomAvailability = $this->get($uuid);
        $this->repository->delete($roomAvailability);
    }

    /**
     * @throws EntityNotFoundException
     */
    public function update(string $uuid, RoomAvailabilityUpdateRequest $roomAvailabilityUpdateRequest): RoomAvailability
    {
        $component = $this->componentRepository->findOneByGoldenId($roomAvailabilityUpdateRequest->componentGoldenId);

        $roomAvailability = $this->get($uuid);

        $roomAvailability->component = $component;
        $roomAvailability->componentGoldenId = $roomAvailabilityUpdateRequest->componentGoldenId;
        $roomAvailability->stock = $roomAvailabilityUpdateRequest->stock;
        $roomAvailability->date = $roomAvailabilityUpdateRequest->date;
        $roomAvailability->type = $roomAvailabilityUpdateRequest->type;

        $this->repository->save($roomAvailability);

        return $roomAvailability;
    }

    public function getRoomAvailabilitiesByComponentGoldenIds(
        array $componentIds,
        string $type,
        \DateTimeInterface $dateFrom,
        \DateTimeInterface $dateTo
    ): array {
        return $this->repository->findRoomAvailabilitiesByComponentGoldenIds($componentIds, $type, $dateFrom, $dateTo);
    }

    public function getRoomAvailabilitiesListByComponentGoldenId(
        string $componentGoldenId,
        string $type,
        \DateTimeInterface $dateFrom,
        \DateTimeInterface $dateTo
    ): array {
        return $this->repository->findAllByComponentGoldenId($componentGoldenId, $type, $dateFrom, $dateTo);
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
                $roomAvailabilityRequest->product->id,
                $roomAvailabilityRequest->dateFrom,
                $roomAvailabilityRequest->dateTo
            );

            foreach ($datePeriod as $date) {
                $roomAvailability = $roomAvailabilityList[$date->format('Y-m-d')] ?? new RoomAvailability();
                if ($roomAvailability->externalUpdatedAt &&
                    $roomAvailabilityRequest->dateTimeUpdated < $roomAvailability->externalUpdatedAt) {
                    $this->logger->warning(OutdatedRoomAvailabilityInformationException::class, $roomAvailabilityRequest->getContext());
                    continue;
                }

                $roomAvailability->componentGoldenId = $roomAvailabilityRequest->product->id;
                $roomAvailability->component = $component;
                $roomAvailability->stock = $roomAvailabilityRequest->quantity;
                $roomAvailability->date = $date;
                $roomAvailability->type = $component->roomStockType;
                $roomAvailability->externalUpdatedAt = $roomAvailabilityRequest->dateTimeUpdated ?? null;

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
