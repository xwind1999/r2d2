<?php

declare(strict_types=1);

namespace App\Manager;

use App\Contract\Request\Internal\RoomAvailability\RoomAvailabilityCreateRequest;
use App\Contract\Request\Internal\RoomAvailability\RoomAvailabilityUpdateRequest;
use App\Entity\RoomAvailability;
use App\Exception\Repository\EntityNotFoundException;
use App\Repository\ComponentRepository;
use App\Repository\RoomAvailabilityRepository;

class RoomAvailabilityManager
{
    protected RoomAvailabilityRepository $repository;

    protected ComponentRepository $componentRepository;

    public function __construct(RoomAvailabilityRepository $repository, ComponentRepository $componentRepository)
    {
        $this->repository = $repository;
        $this->componentRepository = $componentRepository;
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
}
