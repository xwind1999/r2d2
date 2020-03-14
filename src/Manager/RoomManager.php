<?php

declare(strict_types=1);

namespace App\Manager;

use App\Contract\Request\Room\RoomCreateRequest;
use App\Contract\Request\Room\RoomUpdateRequest;
use App\Entity\Room;
use App\Exception\Repository\EntityNotFoundException;
use App\Repository\PartnerRepository;
use App\Repository\RoomRepository;

class RoomManager
{
    protected RoomRepository $repository;

    protected PartnerRepository $partnerRepository;

    public function __construct(RoomRepository $repository, PartnerRepository $partnerRepository)
    {
        $this->repository = $repository;
        $this->partnerRepository = $partnerRepository;
    }

    public function create(RoomCreateRequest $roomCreateRequest): Room
    {
        $partner = $this->partnerRepository->findOneByGoldenId($roomCreateRequest->partnerGoldenId);

        $room = new Room();
        $room->partner = $partner;
        $room->goldenId = $roomCreateRequest->goldenId;
        $room->partnerGoldenId = $roomCreateRequest->partnerGoldenId;
        $room->name = $roomCreateRequest->name;
        $room->description = $roomCreateRequest->description;
        $room->inventory = $roomCreateRequest->inventory;
        $room->isSellable = $roomCreateRequest->isSellable;
        $room->status = $roomCreateRequest->status;

        $this->repository->save($room);

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
        $this->repository->delete($room);
    }

    /**
     * @throws EntityNotFoundException
     */
    public function update(string $uuid, RoomUpdateRequest $roomUpdateRequest): Room
    {
        $partner = $this->partnerRepository->findOneByGoldenId($roomUpdateRequest->partnerGoldenId);

        $room = $this->get($uuid);
        $room->partner = $partner;
        $room->goldenId = $roomUpdateRequest->goldenId;
        $room->partnerGoldenId = $roomUpdateRequest->partnerGoldenId;
        $room->name = $roomUpdateRequest->name;
        $room->description = $roomUpdateRequest->description;
        $room->inventory = $roomUpdateRequest->inventory;
        $room->isSellable = $roomUpdateRequest->isSellable;
        $room->status = $roomUpdateRequest->status;

        $this->repository->save($room);

        return $room;
    }
}
