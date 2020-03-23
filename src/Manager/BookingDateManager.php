<?php

declare(strict_types=1);

namespace App\Manager;

use App\Contract\Request\BookingDate\BookingDateCreateRequest;
use App\Contract\Request\BookingDate\BookingDateUpdateRequest;
use App\Entity\BookingDate;
use App\Exception\Repository\EntityNotFoundException;
use App\Repository\BookingDateRepository;
use App\Repository\BookingRepository;
use App\Repository\RateBandRepository;
use App\Repository\RoomRepository;

class BookingDateManager
{
    protected BookingDateRepository $repository;

    protected RoomRepository $roomRepository;

    protected RateBandRepository $rateBandRepository;

    protected BookingRepository $bookingRepository;

    public function __construct(
        BookingDateRepository $repository,
        RoomRepository $roomRepository,
        RateBandRepository $rateBandRepository,
        BookingRepository $bookingRepository
    ) {
        $this->repository = $repository;
        $this->roomRepository = $roomRepository;
        $this->rateBandRepository = $rateBandRepository;
        $this->bookingRepository = $bookingRepository;
    }

    public function create(BookingDateCreateRequest $bookingDateCreateRequest): BookingDate
    {
        $rateBand = $this->rateBandRepository->findOneByGoldenId($bookingDateCreateRequest->rateBandGoldenId);
        $room = $this->roomRepository->findOneByGoldenId($bookingDateCreateRequest->roomGoldenId);
        $booking = $this->bookingRepository->findOneByGoldenId($bookingDateCreateRequest->bookingGoldenId);

        $bookingDate = new BookingDate();

        $bookingDate->rateBand = $rateBand;
        $bookingDate->room = $room;
        $bookingDate->booking = $booking;
        $bookingDate->bookingGoldenId = $bookingDateCreateRequest->bookingGoldenId;
        $bookingDate->roomGoldenId = $bookingDateCreateRequest->roomGoldenId;
        $bookingDate->rateBandGoldenId = $bookingDateCreateRequest->rateBandGoldenId;
        $bookingDate->date = $bookingDateCreateRequest->date;
        $bookingDate->price = $bookingDateCreateRequest->price;
        $bookingDate->isUpsell = $bookingDateCreateRequest->isUpsell;
        $bookingDate->guestsCount = $bookingDateCreateRequest->guestsCount;

        $this->repository->save($bookingDate);

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
        $this->repository->delete($bookingDate);
    }

    /**
     * @throws EntityNotFoundException
     */
    public function update(string $uuid, BookingDateUpdateRequest $bookingDateUpdateRequest): BookingDate
    {
        $rateBand = $this->rateBandRepository->findOneByGoldenId($bookingDateUpdateRequest->rateBandGoldenId);
        $room = $this->roomRepository->findOneByGoldenId($bookingDateUpdateRequest->roomGoldenId);
        $booking = $this->bookingRepository->findOneByGoldenId($bookingDateUpdateRequest->bookingGoldenId);

        $bookingDate = $this->get($uuid);

        $bookingDate->rateBand = $rateBand;
        $bookingDate->room = $room;
        $bookingDate->booking = $booking;
        $bookingDate->bookingGoldenId = $bookingDateUpdateRequest->bookingGoldenId;
        $bookingDate->roomGoldenId = $room->goldenId;
        $bookingDate->rateBandGoldenId = $rateBand->goldenId;
        $bookingDate->date = $bookingDateUpdateRequest->date;
        $bookingDate->price = $bookingDateUpdateRequest->price;
        $bookingDate->isUpsell = $bookingDateUpdateRequest->isUpsell;
        $bookingDate->guestsCount = $bookingDateUpdateRequest->guestsCount;

        $this->repository->save($bookingDate);

        return $bookingDate;
    }
}
