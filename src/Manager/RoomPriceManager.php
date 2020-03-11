<?php

declare(strict_types=1);

namespace App\Manager;

use App\Contract\Request\RoomPrice\RoomPriceCreateRequest;
use App\Contract\Request\RoomPrice\RoomPriceUpdateRequest;
use App\Entity\RoomPrice;
use App\Exception\Repository\EntityNotFoundException;
use App\Repository\RoomPriceRepository;
use Doctrine\ORM\EntityManagerInterface;

class RoomPriceManager
{
    protected EntityManagerInterface $em;

    protected RoomPriceRepository $repository;

    public function __construct(EntityManagerInterface $em, RoomPriceRepository $repository)
    {
        $this->em = $em;
        $this->repository = $repository;
    }

    public function create(RoomPriceCreateRequest $roomPriceCreateRequest): RoomPrice
    {
        $roomPrice = new RoomPrice();

        $roomPrice->roomGoldenId = $roomPriceCreateRequest->roomGoldenId;
        $roomPrice->rateBandGoldenId = $roomPriceCreateRequest->rateBandGoldenId;
        $roomPrice->date = $roomPriceCreateRequest->date;
        $roomPrice->price = $roomPriceCreateRequest->price;

        $this->em->persist($roomPrice);
        $this->em->flush();

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
        $this->em->remove($roomPrice);
        $this->em->flush();
    }

    /**
     * @throws EntityNotFoundException
     */
    public function update(string $uuid, RoomPriceUpdateRequest $roomPriceUpdateRequest): void
    {
        $roomPrice = $this->get($uuid);

        $roomPrice->roomGoldenId = $roomPriceUpdateRequest->roomGoldenId;
        $roomPrice->rateBandGoldenId = $roomPriceUpdateRequest->rateBandGoldenId;
        $roomPrice->date = $roomPriceUpdateRequest->date;
        $roomPrice->price = $roomPriceUpdateRequest->price;

        $this->em->persist($roomPrice);
        $this->em->flush();
    }
}
