<?php

declare(strict_types=1);

namespace App\Manager;

use App\Contract\Request\RoomAvailability\RoomAvailabilityCreateRequest;
use App\Contract\Request\RoomAvailability\RoomAvailabilityUpdateRequest;
use App\Entity\RoomAvailability;
use App\Exception\Repository\EntityNotFoundException;
use App\Repository\RateBandRepository;
use App\Repository\RoomAvailabilityRepository;
use App\Repository\RoomRepository;

class RoomAvailabilityManager
{
    protected RoomAvailabilityRepository $repository;

    protected RoomRepository $roomRepository;

    protected RateBandRepository $rateBandRepository;

    public function __construct(RoomAvailabilityRepository $repository, RoomRepository $roomRepository, RateBandRepository $rateBandRepository)
    {
        $this->repository = $repository;
        $this->roomRepository = $roomRepository;
        $this->rateBandRepository = $rateBandRepository;
    }

    public function create(RoomAvailabilityCreateRequest $roomAvailabilityCreateRequest): RoomAvailability
    {
        $rateBand = $this->rateBandRepository->findOneByGoldenId($roomAvailabilityCreateRequest->rateBandGoldenId);
        $room = $this->roomRepository->findOneByGoldenId($roomAvailabilityCreateRequest->roomGoldenId);

        $roomAvailability = new RoomAvailability();

        $roomAvailability->rateBand = $rateBand;
        $roomAvailability->room = $room;
        $roomAvailability->roomGoldenId = $roomAvailabilityCreateRequest->roomGoldenId;
        $roomAvailability->rateBandGoldenId = $roomAvailabilityCreateRequest->rateBandGoldenId;
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
        $rateBand = $this->rateBandRepository->findOneByGoldenId($roomAvailabilityUpdateRequest->rateBandGoldenId);
        $room = $this->roomRepository->findOneByGoldenId($roomAvailabilityUpdateRequest->roomGoldenId);

        $roomAvailability = $this->get($uuid);

        $roomAvailability->rateBand = $rateBand;
        $roomAvailability->room = $room;
        $roomAvailability->roomGoldenId = $roomAvailabilityUpdateRequest->roomGoldenId;
        $roomAvailability->rateBandGoldenId = $roomAvailabilityUpdateRequest->rateBandGoldenId;
        $roomAvailability->stock = $roomAvailabilityUpdateRequest->stock;
        $roomAvailability->date = $roomAvailabilityUpdateRequest->date;
        $roomAvailability->type = $roomAvailabilityUpdateRequest->type;

        $this->repository->save($roomAvailability);

        return $roomAvailability;
    }
}
