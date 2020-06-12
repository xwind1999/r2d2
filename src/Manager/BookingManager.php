<?php

declare(strict_types=1);

namespace App\Manager;

use App\Contract\Request\Booking\BookingCreateRequest;
use App\Contract\Request\Booking\BookingUpdateRequest;
use App\DBAL\BookingStatus;
use App\Entity\Booking;
use App\Entity\BookingDate;
use App\Entity\Guest;
use App\Exception\Booking\BadPriceException;
use App\Exception\Booking\BookingAlreadyInFinalStatusException;
use App\Exception\Booking\BookingHasExpiredException;
use App\Exception\Booking\DateOutOfRangeException;
use App\Exception\Booking\DuplicatedDatesForSameRoomException;
use App\Exception\Booking\InvalidBookingNewStatus;
use App\Exception\Booking\InvalidExtraNightException;
use App\Exception\Booking\NoIncludedRoomFoundException;
use App\Exception\Booking\RoomsDontHaveSameDurationException;
use App\Exception\Booking\UnallocatedDateException;
use App\Exception\Http\ResourceConflictException;
use App\Exception\Repository\BookingNotFoundException;
use App\Helper\MoneyHelper;
use App\Repository\BookingRepository;
use App\Repository\BoxExperienceRepository;
use App\Repository\BoxRepository;
use App\Repository\ComponentRepository;
use App\Repository\ExperienceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;

class BookingManager
{
    private const EXPIRATION_TIME = 'PT15M';

    private EntityManagerInterface $em;
    private BookingRepository $repository;
    private ExperienceRepository $experienceRepository;
    private BoxExperienceRepository $boxExperienceRepository;
    private ComponentRepository $componentRepository;
    private MoneyHelper $moneyHelper;
    private BoxRepository $boxRepository;

    public function __construct(
        EntityManagerInterface $em,
        BookingRepository $repository,
        ExperienceRepository $experienceRepository,
        BoxExperienceRepository $boxExperienceRepository,
        ComponentRepository $componentRepository,
        MoneyHelper $moneyHelper,
        BoxRepository $boxRepository
    ) {
        $this->em = $em;
        $this->repository = $repository;
        $this->experienceRepository = $experienceRepository;
        $this->boxExperienceRepository = $boxExperienceRepository;
        $this->componentRepository = $componentRepository;
        $this->moneyHelper = $moneyHelper;
        $this->boxRepository = $boxRepository;
    }

    public function create(BookingCreateRequest $bookingCreateRequest): Booking
    {
        try {
            $this->repository->findOneByGoldenId($bookingCreateRequest->bookingId);

            throw new ResourceConflictException();
        } catch (BookingNotFoundException $exception) {
        }

        $experience = $this->experienceRepository->findOneByGoldenId($bookingCreateRequest->experience->id);
        $box = $this->boxRepository->findOneByGoldenId($bookingCreateRequest->box);
        $this->boxExperienceRepository->findOneEnabledByBoxExperience($box, $experience);
        $component = $this->componentRepository->findDefaultRoomByExperience($experience);

        $partner = $experience->partner;

        $booking = new Booking();
        $booking->partner = $partner;
        $booking->experience = $experience;
        $booking->goldenId = $bookingCreateRequest->bookingId;
        $booking->partnerGoldenId = $partner->goldenId;
        $booking->experienceGoldenId = $bookingCreateRequest->experience->id;
        $booking->voucher = $bookingCreateRequest->voucher;
        $booking->brand = $box->brand;
        $booking->country = $box->country;
        $booking->startDate = $bookingCreateRequest->startDate;
        $booking->endDate = $bookingCreateRequest->endDate;
        $booking->status = BookingStatus::BOOKING_STATUS_CREATED;
        $booking->customerComment = $bookingCreateRequest->customerComment;
        $booking->components = $bookingCreateRequest->experience->components;
        $booking->cancelledAt = null;
        $booking->expiresAt = (new \DateTime('now'))->add(new \DateInterval(self::EXPIRATION_TIME));
        /** @var ArrayCollection<int, BookingDate> */
        $bookingDatesCollection = new ArrayCollection();
        // TODO: replace with experience price after R2D2-209
        $money = $this->moneyHelper->create(400, $bookingCreateRequest->currency);
        $period = new \DatePeriod($booking->startDate, new \DateInterval('P1D'), $booking->endDate);
        $minimumDuration = $component->duration ?? 1;
        $perDay = $money->allocateTo($minimumDuration);
        $totalPrice = 0;
        $includedDaysCount = 0;
        $includedLastDate = (clone $booking->startDate)->modify(sprintf('+%s days', $minimumDuration - 1));

        $bookedDates = [];
        /*
         * Constraints to be verified:
         * 1. number of nights being booked are at least the same as the room duration
         * 2. the booking date should be between the startDate and endDate provided
         * 3. the numbers of nights with price=0 should be the same as the room duration
         * 4. any dates past the minimum booking duration should have extra_night=true set
         * 5. extra rooms must have a price
         * 6. only one room entry can have extra_room=false
         * 7. all rooms should have the same duration
         * 8. cannot have the same date in two entries for a room
         */

        if ($booking->endDate->diff($booking->startDate)->days < $minimumDuration) {
            throw new UnallocatedDateException();
        }

        $processedIncludedRoom = false;

        foreach ($bookingCreateRequest->rooms as $roomIndex => $room) {
            // validating #6
            if ($processedIncludedRoom && !$room->extraRoom) {
                throw new NoIncludedRoomFoundException();
            }

            if (!$processedIncludedRoom) {
                $processedIncludedRoom = !$room->extraRoom;
            }
            $roomDates = [];
            foreach ($room->dates as $date) {
                // validating #2
                if ($date->day < $booking->startDate || $date->day >= $booking->endDate) {
                    throw new DateOutOfRangeException();
                }

                // validating #8
                if (isset($roomDates[$date->day->format('Y-m-d')])) {
                    throw new DuplicatedDatesForSameRoomException();
                }
                $roomDates[$date->day->format('Y-m-d')] = 1;

                $price = $date->price;
                if (0 === $price) {
                    // validating #3 and #5
                    if ($includedDaysCount >= $minimumDuration || true === $room->extraRoom) {
                        throw new BadPriceException();
                    }
                    $price = (int) $perDay[$includedDaysCount]->getAmount();
                    ++$includedDaysCount;
                }

                //validation #4
                if ($date->day > $includedLastDate && false === $date->extraNight) {
                    throw new InvalidExtraNightException();
                }

                $day = $date->day->format('Y-m-d');
                $totalPrice += $price;
                $bookedDates[$day] = isset($bookedDates[$day]) ? $bookedDates[$day] + 1 : 1;

                $bookingDate = new BookingDate();
                $bookingDate->booking = $booking;
                $bookingDate->bookingGoldenId = $booking->goldenId;
                $bookingDate->component = $component;
                $bookingDate->componentGoldenId = $component->goldenId;
                $bookingDate->date = $date->day;
                $bookingDate->price = $price;
                $bookingDate->guestsCount = $experience->peopleNumber ?? 1; //should we?

                $bookingDatesCollection->add($bookingDate);
                $this->em->persist($bookingDate);
            }
        }

        $booking->bookingDate = $bookingDatesCollection;

        //compare the dates allocated in the rooms with the date range sent in the root element
        foreach ($period as $date) {
            if (!isset($bookedDates[$date->format('Y-m-d')])) {
                throw new UnallocatedDateException();
            }
        }

        // validation #7
        if (count(array_flip($bookedDates)) > 1) {
            throw new RoomsDontHaveSameDurationException();
        }

        $booking->totalPrice = $totalPrice;

        /** @var ArrayCollection<int, Guest> */
        $guestCollection = new ArrayCollection();
        foreach ($bookingCreateRequest->guests as $guestRequest) {
            $guest = new Guest();
            $guest->booking = $booking;
            $guest->bookingGoldenId = $booking->goldenId;
            $guest->firstName = $guestRequest->firstName;
            $guest->lastName = $guestRequest->lastName;
            $guest->email = $guestRequest->email;
            $guest->phone = $guestRequest->phone;

            $guestCollection->add($guest);
            $this->em->persist($guest);
        }

        $booking->guest = $guestCollection;

        $this->em->persist($booking);
        $this->em->flush();

        return $booking;
    }

    public function update(BookingUpdateRequest $bookingUpdateRequest): void
    {
        $booking = $this->repository->findOneByGoldenId($bookingUpdateRequest->bookingId);
        $this->validateBookingStatus($bookingUpdateRequest, $booking);

        $booking->status = $bookingUpdateRequest->status;

        if (null !== $bookingUpdateRequest->voucher) {
            $booking->voucher = $bookingUpdateRequest->voucher;
        }

        $this->em->persist($booking);
        $this->em->flush();

        //TODO: send the booking to CMHub
        //TODO: send the booking cancellation to CMHub
        //maybe dispatch a "BookingUpdatedEvent" with the new status?
    }

    private function validateBookingExpirationTime(Booking $booking): void
    {
        $dateNow = new \DateTime('now');

        // @TODO: Check the availability before send the exception
        if ($booking->expiresAt < $dateNow) {
            throw new BookingHasExpiredException();
        }
    }

    private function validateBookingStatus(BookingUpdateRequest $bookingUpdateRequest, Booking $booking): void
    {
        $this->validateBookingExpirationTime($booking);

        if (BookingStatus::BOOKING_STATUS_COMPLETE === $booking->status || BookingStatus::BOOKING_STATUS_CANCELLED === $booking->status) {
            throw new BookingAlreadyInFinalStatusException();
        }

        if ($booking->status === $bookingUpdateRequest->status) {
            throw new InvalidBookingNewStatus();
        }
    }
}
