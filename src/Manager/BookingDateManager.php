<?php

declare(strict_types=1);

namespace App\Manager;

use App\Contract\Request\BookingDate\BookingDateCreateRequest;
use App\Contract\Request\BookingDate\BookingDateUpdateRequest;
use App\Entity\BookingDate;
use App\Exception\Repository\EntityNotFoundException;
use App\Repository\BookingDateRepository;
use Doctrine\ORM\EntityManagerInterface;

class BookingDateManager
{
    protected EntityManagerInterface $em;

    protected BookingDateRepository $repository;

    public function __construct(EntityManagerInterface $em, BookingDateRepository $repository)
    {
        $this->em = $em;
        $this->repository = $repository;
    }

    public function create(BookingDateCreateRequest $bookingDateCreateRequest): BookingDate
    {
        $bookingDate = new BookingDate();

        $bookingDate->bookingGoldenId = $bookingDateCreateRequest->bookingGoldenId;
        $bookingDate->roomGoldenId = $bookingDateCreateRequest->roomGoldenId;
        $bookingDate->rateBandGoldenId = $bookingDateCreateRequest->rateBandGoldenId;
        $bookingDate->date = $bookingDateCreateRequest->date;
        $bookingDate->price = $bookingDateCreateRequest->price;
        $bookingDate->isUpsell = $bookingDateCreateRequest->isUpsell;
        $bookingDate->guestsCount = $bookingDateCreateRequest->guestsCount;

        $this->em->persist($bookingDate);
        $this->em->flush();

        return $bookingDate;
    }

    /**
     * @throws EntityNotFoundException
     */
    public function get(string $uuid): BookingDate
    {
        return $this->repository->findOne($uuid);
    }

    /**
     * @throws EntityNotFoundException
     */
    public function delete(string $uuid): void
    {
        $bookingDate = $this->get($uuid);
        $this->em->remove($bookingDate);
        $this->em->flush();
    }

    /**
     * @throws EntityNotFoundException
     */
    public function update(string $uuid, BookingDateUpdateRequest $bookingDateUpdateRequest): void
    {
        $bookingDate = $this->get($uuid);

        $bookingDate->roomGoldenId = $bookingDateUpdateRequest->roomGoldenId;
        $bookingDate->rateBandGoldenId = $bookingDateUpdateRequest->rateBandGoldenId;
        $bookingDate->date = $bookingDateUpdateRequest->date;
        $bookingDate->price = $bookingDateUpdateRequest->price;
        $bookingDate->isUpsell = $bookingDateUpdateRequest->isUpsell;
        $bookingDate->guestsCount = $bookingDateUpdateRequest->guestsCount;

        $this->em->persist($bookingDate);
        $this->em->flush();
    }
}
