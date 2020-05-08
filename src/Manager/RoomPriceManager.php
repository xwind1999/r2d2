<?php

declare(strict_types=1);

namespace App\Manager;

use App\Contract\Request\Internal\RoomPrice\RoomPriceCreateRequest;
use App\Contract\Request\Internal\RoomPrice\RoomPriceUpdateRequest;
use App\Entity\RoomPrice;
use App\Exception\Repository\EntityNotFoundException;
use App\Repository\ComponentRepository;
use App\Repository\RoomPriceRepository;

class RoomPriceManager
{
    protected RoomPriceRepository $repository;

    protected ComponentRepository $componentRepository;

    public function __construct(RoomPriceRepository $repository, ComponentRepository $componentRepository)
    {
        $this->repository = $repository;
        $this->componentRepository = $componentRepository;
    }

    public function create(RoomPriceCreateRequest $roomPriceCreateRequest): RoomPrice
    {
        $component = $this->componentRepository->findOneByGoldenId($roomPriceCreateRequest->componentGoldenId);

        $roomPrice = new RoomPrice();

        $roomPrice->component = $component;
        $roomPrice->componentGoldenId = $roomPriceCreateRequest->componentGoldenId;
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
        $component = $this->componentRepository->findOneByGoldenId($roomPriceUpdateRequest->componentGoldenId);

        $roomPrice = $this->get($uuid);

        $roomPrice->component = $component;
        $roomPrice->componentGoldenId = $roomPriceUpdateRequest->componentGoldenId;
        $roomPrice->date = $roomPriceUpdateRequest->date;
        $roomPrice->price = $roomPriceUpdateRequest->price;

        $this->repository->save($roomPrice);

        return $roomPrice;
    }
}
