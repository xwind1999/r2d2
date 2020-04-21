<?php

declare(strict_types=1);

namespace App\Manager;

use App\Contract\Request\BroadcastListener\ProductRequest;
use App\Contract\Request\Room\RoomCreateRequest;
use App\Contract\Request\Room\RoomUpdateRequest;
use App\Entity\Room;
use App\Exception\Repository\EntityNotFoundException;
use App\Exception\Repository\PartnerNotFoundException;
use App\Exception\Repository\RoomNotFoundException;
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
        $room->duration = $roomCreateRequest->voucherExpirationDuration;
        $room->isSellable = $roomCreateRequest->isSellable;
        $room->isReservable = $roomCreateRequest->isReservable;
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
        $room->duration = $roomUpdateRequest->voucherExpirationDuration;
        $room->isSellable = $roomUpdateRequest->isSellable;
        $room->isReservable = $roomUpdateRequest->isReservable;
        $room->status = $roomUpdateRequest->status;

        $this->repository->save($room);

        return $room;
    }

    /**
     * @throws PartnerNotFoundException
     */
    public function replace(ProductRequest $productRequest): void
    {
        $partner = $this->partnerRepository->findOneByGoldenId($productRequest->partnerGoldenId);

        try {
            $component = $this->repository->findOneByGoldenId($productRequest->goldenId);
        } catch (RoomNotFoundException $exception) {
            $component = new Room();
        }

        $component->goldenId = $productRequest->goldenId;
        $component->partner = $partner;
        $component->partnerGoldenId = $productRequest->partnerGoldenId;
        $component->name = $productRequest->name;
        $component->description = $productRequest->description;
        $component->duration = $productRequest->voucherExpirationDuration;
        $component->isReservable = $productRequest->isReservable;
        $component->isSellable = $productRequest->isSellable;
        $component->status = $productRequest->status;

        $this->repository->save($component);
    }
}
