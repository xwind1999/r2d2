<?php

declare(strict_types=1);

namespace App\Manager;

use App\Contract\Request\Room\RoomCreateRequest;
use App\Contract\Request\Room\RoomUpdateRequest;
use App\Entity\Room;
use App\Exception\Repository\EntityNotFoundException;
use App\Repository\RoomRepository;
use Doctrine\ORM\EntityManagerInterface;

class RoomManager
{
    protected EntityManagerInterface $em;

    protected RoomRepository $repository;

    public function __construct(EntityManagerInterface $em, RoomRepository $repository)
    {
        $this->em = $em;
        $this->repository = $repository;
    }

    public function create(RoomCreateRequest $roomCreateRequest): Room
    {
        $room = new Room();

        $room->goldenId = $roomCreateRequest->goldenId;
        $room->partnerGoldenId = $roomCreateRequest->partnerGoldenId;
        $room->name = $roomCreateRequest->name;
        $room->description = $roomCreateRequest->description;
        $room->inventory = $roomCreateRequest->inventory;
        $room->isSellable = $roomCreateRequest->isSellable;
        $room->status = $roomCreateRequest->status;

        $this->em->persist($room);
        $this->em->flush();

        return $room;
    }

    /**
     * @throws EntityNotFoundException
     */
    public function get(string $uuid): Room
    {
        return $this->repository->findOne($uuid);
    }

    /**
     * @throws EntityNotFoundException
     */
    public function delete(string $uuid): void
    {
        $room = $this->get($uuid);
        $this->em->remove($room);
        $this->em->flush();
    }

    /**
     * @throws EntityNotFoundException
     */
    public function update(string $uuid, RoomUpdateRequest $roomUpdateRequest): void
    {
        $room = $this->get($uuid);

        $room->goldenId = $roomUpdateRequest->goldenId;
        $room->partnerGoldenId = $roomUpdateRequest->partnerGoldenId;
        $room->name = $roomUpdateRequest->name;
        $room->description = $roomUpdateRequest->description;
        $room->inventory = $roomUpdateRequest->inventory;
        $room->isSellable = $roomUpdateRequest->isSellable;
        $room->status = $roomUpdateRequest->status;

        $this->em->persist($room);
        $this->em->flush();
    }
}
