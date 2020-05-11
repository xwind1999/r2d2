<?php

declare(strict_types=1);

namespace App\Manager;

use App\Contract\Request\Internal\BookingDate\BookingDateCreateRequest;
use App\Contract\Request\Internal\BookingDate\BookingDateUpdateRequest;
use App\Entity\BookingDate;
use App\Exception\Repository\EntityNotFoundException;
use App\Repository\BookingDateRepository;
use App\Repository\BookingRepository;
use App\Repository\ComponentRepository;

class BookingDateManager
{
    protected BookingDateRepository $repository;

    protected ComponentRepository $componentRepository;

    protected BookingRepository $bookingRepository;

    public function __construct(
        BookingDateRepository $repository,
        ComponentRepository $componentRepository,
        BookingRepository $bookingRepository
    ) {
        $this->repository = $repository;
        $this->componentRepository = $componentRepository;
        $this->bookingRepository = $bookingRepository;
    }

    public function create(BookingDateCreateRequest $bookingDateCreateRequest): BookingDate
    {
        $component = $this->componentRepository->findOneByGoldenId($bookingDateCreateRequest->componentGoldenId);
        $booking = $this->bookingRepository->findOneByGoldenId($bookingDateCreateRequest->bookingGoldenId);

        $bookingDate = new BookingDate();

        $bookingDate->component = $component;
        $bookingDate->booking = $booking;
        $bookingDate->bookingGoldenId = $bookingDateCreateRequest->bookingGoldenId;
        $bookingDate->componentGoldenId = $bookingDateCreateRequest->componentGoldenId;
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
        $component = $this->componentRepository->findOneByGoldenId($bookingDateUpdateRequest->componentGoldenId);
        $booking = $this->bookingRepository->findOneByGoldenId($bookingDateUpdateRequest->bookingGoldenId);

        $bookingDate = $this->get($uuid);

        $bookingDate->component = $component;
        $bookingDate->booking = $booking;
        $bookingDate->bookingGoldenId = $bookingDateUpdateRequest->bookingGoldenId;
        $bookingDate->componentGoldenId = $component->goldenId;
        $bookingDate->date = $bookingDateUpdateRequest->date;
        $bookingDate->price = $bookingDateUpdateRequest->price;
        $bookingDate->isUpsell = $bookingDateUpdateRequest->isUpsell;
        $bookingDate->guestsCount = $bookingDateUpdateRequest->guestsCount;

        $this->repository->save($bookingDate);

        return $bookingDate;
    }
}
