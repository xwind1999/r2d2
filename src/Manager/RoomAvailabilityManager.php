<?php

declare(strict_types=1);

namespace App\Manager;

use App\Contract\Request\RoomAvailability\RoomAvailabilityCreateRequest;
use App\Contract\Request\RoomAvailability\RoomAvailabilityUpdateRequest;
use App\Entity\RoomAvailability;
use App\Exception\Repository\EntityNotFoundException;
use App\Repository\RoomAvailabilityRepository;
use Doctrine\ORM\EntityManagerInterface;

class RoomAvailabilityManager
{
    protected EntityManagerInterface $em;

    protected RoomAvailabilityRepository $repository;

    public function __construct(EntityManagerInterface $em, RoomAvailabilityRepository $repository)
    {
        $this->em = $em;
        $this->repository = $repository;
    }

    public function create(RoomAvailabilityCreateRequest $roomAvailabilityCreateRequest): RoomAvailability
    {
        $roomAvailability = new RoomAvailability();

        $roomAvailability->roomGoldenId = $roomAvailabilityCreateRequest->roomGoldenId;
        $roomAvailability->rateBandGoldenId = $roomAvailabilityCreateRequest->rateBandGoldenId;
        $roomAvailability->stock = $roomAvailabilityCreateRequest->stock;
        $roomAvailability->date = $roomAvailabilityCreateRequest->date;
        $roomAvailability->type = $roomAvailabilityCreateRequest->type;

        $this->em->persist($roomAvailability);
        $this->em->flush();

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
        $this->em->remove($roomAvailability);
        $this->em->flush();
    }

    /**
     * @throws EntityNotFoundException
     */
    public function update(string $uuid, RoomAvailabilityUpdateRequest $roomAvailabilityUpdateRequest): void
    {
        $roomAvailability = $this->get($uuid);

        $roomAvailability->roomGoldenId = $roomAvailabilityUpdateRequest->roomGoldenId;
        $roomAvailability->rateBandGoldenId = $roomAvailabilityUpdateRequest->rateBandGoldenId;
        $roomAvailability->stock = $roomAvailabilityUpdateRequest->stock;
        $roomAvailability->date = $roomAvailabilityUpdateRequest->date;
        $roomAvailability->type = $roomAvailabilityUpdateRequest->type;

        $this->em->persist($roomAvailability);
        $this->em->flush();
    }
}
