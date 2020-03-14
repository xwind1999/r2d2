<?php

declare(strict_types=1);

namespace App\Manager;

use App\Contract\Request\RoomPrice\RoomPriceCreateRequest;
use App\Contract\Request\RoomPrice\RoomPriceUpdateRequest;
use App\Entity\RoomPrice;
use App\Exception\Repository\EntityNotFoundException;
use App\Repository\RateBandRepository;
use App\Repository\RoomPriceRepository;
use App\Repository\RoomRepository;

class RoomPriceManager
{
    protected RoomPriceRepository $repository;

    protected RoomRepository $roomRepository;

    protected RateBandRepository $rateBandRepository;

    public function __construct(RoomPriceRepository $repository, RoomRepository $roomRepository, RateBandRepository $rateBandRepository)
    {
        $this->repository = $repository;
        $this->roomRepository = $roomRepository;
        $this->rateBandRepository = $rateBandRepository;
    }

    public function create(RoomPriceCreateRequest $roomPriceCreateRequest): RoomPrice
    {
        $rateBand = $this->rateBandRepository->findOneByGoldenId($roomPriceCreateRequest->rateBandGoldenId);
        $room = $this->roomRepository->findOneByGoldenId($roomPriceCreateRequest->roomGoldenId);

        $roomPrice = new RoomPrice();

        $roomPrice->rateBand = $rateBand;
        $roomPrice->room = $room;
        $roomPrice->roomGoldenId = $roomPriceCreateRequest->roomGoldenId;
        $roomPrice->rateBandGoldenId = $roomPriceCreateRequest->rateBandGoldenId;
        $roomPrice->date = $roomPriceCreateRequest->date;
        $roomPrice->price = $roomPriceCreateRequest->price;

        $this->repository->save($roomPrice);

        return $roomPrice;
    }

    /**
     * @throws EntityNotFoundException
     */
    public function get(string $uuid): RoomPrice
    {
        return $this->repository->findOne($uuid);
    }

    /**
     * @throws EntityNotFoundException
     */
    public function delete(string $uuid): void
    {
        $roomPrice = $this->get($uuid);
        $this->repository->delete($roomPrice);
    }

    /**
     * @throws EntityNotFoundException
     */
    public function update(string $uuid, RoomPriceUpdateRequest $roomPriceUpdateRequest): RoomPrice
    {
        $rateBand = $this->rateBandRepository->findOneByGoldenId($roomPriceUpdateRequest->rateBandGoldenId);
        $room = $this->roomRepository->findOneByGoldenId($roomPriceUpdateRequest->roomGoldenId);

        $roomPrice = $this->get($uuid);

        $roomPrice->rateBand = $rateBand;
        $roomPrice->room = $room;
        $roomPrice->roomGoldenId = $roomPriceUpdateRequest->roomGoldenId;
        $roomPrice->rateBandGoldenId = $roomPriceUpdateRequest->rateBandGoldenId;
        $roomPrice->date = $roomPriceUpdateRequest->date;
        $roomPrice->price = $roomPriceUpdateRequest->price;

        $this->repository->save($roomPrice);

        return $roomPrice;
    }
}
