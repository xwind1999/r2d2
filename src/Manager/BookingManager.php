<?php

declare(strict_types=1);

namespace App\Manager;

use App\Contract\Request\Booking\BookingCreateRequest;
use App\Contract\Request\Booking\BookingUpdateRequest;
use App\Entity\Booking;
use App\Entity\Guest;
use App\Exception\Repository\EntityNotFoundException;
use App\Repository\BookingRepository;
use App\Repository\ExperienceRepository;
use App\Repository\GuestRepository;
use App\Repository\PartnerRepository;
use Doctrine\Common\Collections\ArrayCollection;

class BookingManager
{
    private BookingRepository $repository;
    private PartnerRepository $partnerRepository;
    private ExperienceRepository $experienceRepository;
    private GuestRepository $guestRepository;

    public function __construct(
        BookingRepository $repository,
        PartnerRepository $partnerRepository,
        ExperienceRepository $experienceRepository,
        GuestRepository $guestRepository
    ) {
        $this->repository = $repository;
        $this->partnerRepository = $partnerRepository;
        $this->experienceRepository = $experienceRepository;
        $this->guestRepository = $guestRepository;
    }

    public function create(BookingCreateRequest $bookingCreateRequest): Booking
    {
        $partner = $this->partnerRepository->findOneByGoldenId($bookingCreateRequest->partnerGoldenId);
        $experience = $this->experienceRepository->findOneByGoldenId($bookingCreateRequest->experienceGoldenId);

        $booking = new Booking();
        $booking->partner = $partner;
        $booking->experience = $experience;
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
        $booking->customerComment = $bookingCreateRequest->customerComment;
        $booking->partnerComment = $bookingCreateRequest->partnerComment;
        $booking->placedAt = $bookingCreateRequest->placedAt;
        $booking->cancelledAt = $bookingCreateRequest->cancelledAt;

        /** @var ArrayCollection<int, Guest> */
        $guestCollection = new ArrayCollection();
        foreach ($bookingCreateRequest->guest as $guestRequest) {
            $guest = new Guest();
            $guest->booking = $booking;
            $guest->bookingGoldenId = $bookingCreateRequest->goldenId;
            $guest->firstName = $guestRequest->firstName;
            $guest->lastName = $guestRequest->lastName;
            $guest->email = $guestRequest->email;
            $guest->phone = $guestRequest->phone;

            $guestCollection->add($guest);
            $this->guestRepository->save($guest);
        }

        $booking->guest = $guestCollection;
        $this->repository->save($booking);
        $this->guestRepository->flush();

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
        $this->repository->delete($booking);
    }

    /**
     * @throws EntityNotFoundException
     */
    public function update(string $uuid, BookingUpdateRequest $bookingUpdateRequest): Booking
    {
        $partner = $this->partnerRepository->findOneByGoldenId($bookingUpdateRequest->partnerGoldenId);
        $experience = $this->experienceRepository->findOneByGoldenId($bookingUpdateRequest->experienceGoldenId);

        $booking = $this->get($uuid);

        $booking->partner = $partner;
        $booking->experience = $experience;
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
        $booking->customerComment = $bookingUpdateRequest->customerComment;
        $booking->partnerComment = $bookingUpdateRequest->partnerComment;
        $booking->placedAt = $bookingUpdateRequest->placedAt;
        $booking->cancelledAt = $bookingUpdateRequest->cancelledAt;

        $this->repository->save($booking);

        return $booking;
    }
}
