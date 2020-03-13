<?php

declare(strict_types=1);

namespace App\Manager;

use App\Contract\Request\Booking\BookingCreateRequest;
use App\Contract\Request\Booking\BookingUpdateRequest;
use App\Entity\Booking;
use App\Exception\Repository\EntityNotFoundException;
use App\Repository\BookingRepository;
use Doctrine\ORM\EntityManagerInterface;

class BookingManager
{
    protected EntityManagerInterface $em;

    protected BookingRepository $repository;

    public function __construct(EntityManagerInterface $em, BookingRepository $repository)
    {
        $this->em = $em;
        $this->repository = $repository;
    }

    public function create(BookingCreateRequest $bookingCreateRequest): Booking
    {
        $booking = new Booking();

        $booking->goldenId = $bookingCreateRequest->goldenId;
        $booking->partnerGoldenId = $bookingCreateRequest->partnerGoldenId;
        $booking->experienceGoldenId = $bookingCreateRequest->experienceGoldenId;
        $booking->type = $bookingCreateRequest->type;
        $booking->voucher = $bookingCreateRequest->voucher;
        $booking->brand = $bookingCreateRequest->brand;
        $booking->country = $bookingCreateRequest->country;
        $booking->requestType = $bookingCreateRequest->requestType;
        $booking->channel = $bookingCreateRequest->channel;
        $booking->cancellationChannel = $bookingCreateRequest->cancellationChannel;
        $booking->status = $bookingCreateRequest->status;
        $booking->totalPrice = $bookingCreateRequest->totalPrice;
        $booking->startDate = $bookingCreateRequest->startDate;
        $booking->endDate = $bookingCreateRequest->endDate;
        $booking->customerExternalId = $bookingCreateRequest->customerExternalId;
        $booking->customerFirstName = $bookingCreateRequest->customerFirstName;
        $booking->customerLastName = $bookingCreateRequest->customerLastName;
        $booking->customerEmail = $bookingCreateRequest->customerEmail;
        $booking->customerPhone = $bookingCreateRequest->customerPhone;
        $booking->customerComment = $bookingCreateRequest->customerComment;
        $booking->partnerComment = $bookingCreateRequest->partnerComment;
        $booking->placedAt = $bookingCreateRequest->placedAt;
        $booking->cancelledAt = $bookingCreateRequest->cancelledAt;

        $this->em->persist($booking);
        $this->em->flush();

        return $booking;
    }

    /**
     * @throws EntityNotFoundException
     */
    public function get(string $uuid): Booking
    {
        return $this->repository->findOne($uuid);
    }

    /**
     * @throws EntityNotFoundException
     */
    public function delete(string $uuid): void
    {
        $booking = $this->get($uuid);
        $this->em->remove($booking);
        $this->em->flush();
    }

    /**
     * @throws EntityNotFoundException
     */
    public function update(string $uuid, BookingUpdateRequest $bookingUpdateRequest): void
    {
        $booking = $this->get($uuid);

        $booking->goldenId = $bookingUpdateRequest->goldenId;
        $booking->partnerGoldenId = $bookingUpdateRequest->partnerGoldenId;
        $booking->experienceGoldenId = $bookingUpdateRequest->experienceGoldenId;
        $booking->type = $bookingUpdateRequest->type;
        $booking->voucher = $bookingUpdateRequest->voucher;
        $booking->brand = $bookingUpdateRequest->brand;
        $booking->country = $bookingUpdateRequest->country;
        $booking->requestType = $bookingUpdateRequest->requestType;
        $booking->channel = $bookingUpdateRequest->channel;
        $booking->cancellationChannel = $bookingUpdateRequest->cancellationChannel;
        $booking->status = $bookingUpdateRequest->status;
        $booking->totalPrice = $bookingUpdateRequest->totalPrice;
        $booking->startDate = $bookingUpdateRequest->startDate;
        $booking->endDate = $bookingUpdateRequest->endDate;
        $booking->customerExternalId = $bookingUpdateRequest->customerExternalId;
        $booking->customerFirstName = $bookingUpdateRequest->customerFirstName;
        $booking->customerLastName = $bookingUpdateRequest->customerLastName;
        $booking->customerEmail = $bookingUpdateRequest->customerEmail;
        $booking->customerPhone = $bookingUpdateRequest->customerPhone;
        $booking->customerComment = $bookingUpdateRequest->customerComment;
        $booking->partnerComment = $bookingUpdateRequest->partnerComment;
        $booking->placedAt = $bookingUpdateRequest->placedAt;
        $booking->cancelledAt = $bookingUpdateRequest->cancelledAt;

        $this->em->persist($booking);
        $this->em->flush();
    }
}
