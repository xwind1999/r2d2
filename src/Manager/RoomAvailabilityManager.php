<?php

declare(strict_types=1);

namespace App\Manager;

use App\Contract\Request\RoomAvailability\RoomAvailabilityCreateRequest;
use App\Contract\Request\RoomAvailability\RoomAvailabilityUpdateRequest;
use App\Entity\RoomAvailability;
use App\Exception\Repository\EntityNotFoundException;
use App\Repository\ComponentRepository;
use App\Repository\RateBandRepository;
use App\Repository\RoomAvailabilityRepository;

class RoomAvailabilityManager
{
    protected RoomAvailabilityRepository $repository;

    protected ComponentRepository $componentRepository;

    protected RateBandRepository $rateBandRepository;

    public function __construct(RoomAvailabilityRepository $repository, ComponentRepository $componentRepository, RateBandRepository $rateBandRepository)
    {
        $this->repository = $repository;
        $this->componentRepository = $componentRepository;
        $this->rateBandRepository = $rateBandRepository;
    }

    public function create(RoomAvailabilityCreateRequest $roomAvailabilityCreateRequest): RoomAvailability
    {
        $rateBand = $this->rateBandRepository->findOneByGoldenId($roomAvailabilityCreateRequest->rateBandGoldenId);
        $component = $this->componentRepository->findOneByGoldenId($roomAvailabilityCreateRequest->componentGoldenId);

        $roomAvailability = new RoomAvailability();

        $roomAvailability->rateBand = $rateBand;
        $roomAvailability->component = $component;
        $roomAvailability->componentGoldenId = $roomAvailabilityCreateRequest->componentGoldenId;
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
        $component = $this->componentRepository->findOneByGoldenId($roomAvailabilityUpdateRequest->componentGoldenId);

        $roomAvailability = $this->get($uuid);

        $roomAvailability->rateBand = $rateBand;
        $roomAvailability->component = $component;
        $roomAvailability->componentGoldenId = $roomAvailabilityUpdateRequest->componentGoldenId;
        $roomAvailability->rateBandGoldenId = $roomAvailabilityUpdateRequest->rateBandGoldenId;
        $roomAvailability->stock = $roomAvailabilityUpdateRequest->stock;
        $roomAvailability->date = $roomAvailabilityUpdateRequest->date;
        $roomAvailability->type = $roomAvailabilityUpdateRequest->type;

        $this->repository->save($roomAvailability);

        return $roomAvailability;
    }
}
