<?php

declare(strict_types=1);

namespace App\Manager;

use App\Constants\DateTimeConstants;
use App\Constraint\AvailabilityTypeConstraint;
use App\Constraint\BookingChannelConstraint;
use App\Constraint\BookingStatusConstraint;
use App\Constraint\ProductDurationUnitConstraint;
use App\Constraint\RoomStockTypeConstraint;
use App\Contract\Request\Booking\BookingCreateRequest;
use App\Contract\Request\Booking\BookingImport\BookingImportRequest;
use App\Contract\Request\Booking\BookingUpdateRequest;
use App\Entity\Booking;
use App\Entity\BookingDate;
use App\Entity\Component;
use App\Entity\Guest;
use App\Event\Booking\BookingStatusEvent;
use App\Exception\Booking\BadPriceException;
use App\Exception\Booking\BookingAlreadyInFinalStatusException;
use App\Exception\Booking\BookingHasExpiredException;
use App\Exception\Booking\CurrencyMismatchException;
use App\Exception\Booking\DateOutOfRangeException;
use App\Exception\Booking\DuplicatedDatesForSameRoomException;
use App\Exception\Booking\InvalidBoxBrandException;
use App\Exception\Booking\InvalidBoxCountryException;
use App\Exception\Booking\InvalidBoxCurrencyException;
use App\Exception\Booking\InvalidExperienceComponentListException;
use App\Exception\Booking\InvalidExtraNightException;
use App\Exception\Booking\MisconfiguredExperiencePriceException;
use App\Exception\Booking\NoIncludedRoomFoundException;
use App\Exception\Booking\RoomsDontHaveSameDurationException;
use App\Exception\Booking\UnallocatedDateException;
use App\Exception\Booking\UnavailableDateException;
use App\Exception\Http\ResourceConflictException;
use App\Exception\Http\UnprocessableEntityException;
use App\Exception\Repository\BookingNotFoundException;
use App\Helper\MoneyHelper;
use App\Repository\BookingRepository;
use App\Repository\BoxExperienceRepository;
use App\Repository\BoxRepository;
use App\Repository\ComponentRepository;
use App\Repository\ExperienceRepository;
use App\Repository\RoomAvailabilityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

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
    private EventDispatcherInterface $eventDispatcher;
    private RoomAvailabilityRepository $roomAvailabilityRepository;

    public function __construct(
        EntityManagerInterface $em,
        BookingRepository $repository,
        ExperienceRepository $experienceRepository,
        BoxExperienceRepository $boxExperienceRepository,
        ComponentRepository $componentRepository,
        MoneyHelper $moneyHelper,
        BoxRepository $boxRepository,
        EventDispatcherInterface $eventDispatcher,
        RoomAvailabilityRepository $roomAvailabilityRepository
    ) {
        $this->em = $em;
        $this->repository = $repository;
        $this->experienceRepository = $experienceRepository;
        $this->boxExperienceRepository = $boxExperienceRepository;
        $this->componentRepository = $componentRepository;
        $this->moneyHelper = $moneyHelper;
        $this->boxRepository = $boxRepository;
        $this->eventDispatcher = $eventDispatcher;
        $this->roomAvailabilityRepository = $roomAvailabilityRepository;
    }

    public function create(BookingCreateRequest $bookingCreateRequest): Booking
    {
        if (empty($bookingCreateRequest->experience->components)) {
            throw new InvalidExperienceComponentListException();
        }

        try {
            $this->repository->findOneByGoldenId($bookingCreateRequest->bookingId);

            throw new ResourceConflictException();
        } catch (BookingNotFoundException $exception) {
        }

        $experience = $this->experienceRepository->findOneByGoldenId($bookingCreateRequest->experience->id);

        if (empty($experience->price) || empty($experience->currency)) {
            throw new MisconfiguredExperiencePriceException();
        }

        $box = $this->boxRepository->findOneByGoldenId($bookingCreateRequest->box);

        if (empty($box->currency)) {
            throw new InvalidBoxCurrencyException();
        }

        if (empty($box->brand)) {
            throw new InvalidBoxBrandException();
        }

        if (empty($box->country)) {
            throw new InvalidBoxCountryException();
        }

        $this->boxExperienceRepository->findOneEnabledByBoxExperience($box, $experience);
        $component = $this->componentRepository->findDefaultRoomByExperience($experience);
        $roomStockType = $component->roomStockType;

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
        $booking->status = BookingStatusConstraint::BOOKING_STATUS_CREATED;
        $booking->customerComment = $bookingCreateRequest->customerComment;
        $booking->components = $bookingCreateRequest->experience->components;
        $booking->cancelledAt = null;
        $booking->expiredAt = (new \DateTime('now'))->add(new \DateInterval(self::EXPIRATION_TIME));
        /** @var ArrayCollection<int, BookingDate> */
        $bookingDatesCollection = new ArrayCollection();
        $bookingCurrency = strtoupper($bookingCreateRequest->currency);
        $booking->currency = $bookingCurrency;
        $money = $this->moneyHelper->create($experience->price, $experience->currency);
        $period = new \DatePeriod(
            $booking->startDate,
            new \DateInterval(DateTimeConstants::PLUS_ONE_DAY_DATE_INTERVAL),
            $booking->endDate
        );
        $minimumDuration = $component->duration ?? ProductDurationUnitConstraint::MINIMUM_DURATION;
        $perDay = $money->allocateTo($minimumDuration);
        $totalPrice = 0;
        $includedDaysCount = 0;
        $includedLastDate = (clone $booking->startDate)->modify(sprintf('+%s days', $minimumDuration - 1));
        if (isset($bookingCreateRequest->availabilityType)) {
            if ($this->isValidAvailabilityType($bookingCreateRequest->availabilityType, $component)) {
                $booking->availabilityType = $bookingCreateRequest->availabilityType;
            } else {
                throw new UnprocessableEntityException();
            }
        }

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
         * 9. do not allow mismatch between box currency and partner currency with upsell
         * 10. validate the availability for the booking date and component duration
         * 11. validate if any stop sales in on_request booking
         */

        if ($booking->endDate->diff($booking->startDate)->days < $minimumDuration) {
            throw new UnallocatedDateException();
        }

        // validation #10
        if (RoomStockTypeConstraint::ROOM_STOCK_TYPE_ON_REQUEST !== $roomStockType &&
            $this->isUnavailableForBookings($booking)
        ) {
            throw new UnavailableDateException();
        }

        // validation #11
        if (RoomStockTypeConstraint::ROOM_STOCK_TYPE_ON_REQUEST === $roomStockType &&
            $this->hasStopSaleOnRequestBookings($booking)
        ) {
            throw new UnavailableDateException();
        }

        $processedIncludedRoom = false;

        // validation #9
        $boxCurrency = strtoupper($box->currency);
        $partnerCurrency = strtoupper($partner->currency);
        $isDifferentCurrency =
            $partnerCurrency !== $boxCurrency
            || $bookingCurrency !== $boxCurrency
            || $partnerCurrency !== $bookingCurrency
        ;

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
                if (isset($roomDates[$date->day->format(DateTimeConstants::DEFAULT_DATE_FORMAT)])) {
                    throw new DuplicatedDatesForSameRoomException();
                }
                $roomDates[$date->day->format(DateTimeConstants::DEFAULT_DATE_FORMAT)] = 1;

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

                $day = $date->day->format(DateTimeConstants::DEFAULT_DATE_FORMAT);

                // validation #9
                if ($isDifferentCurrency && (true === $date->extraNight || true === $room->extraRoom)) {
                    throw new CurrencyMismatchException();
                }

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
                $bookingDate->isExtraRoom = $room->extraRoom;
                $bookingDate->isExtraNight = $date->extraNight;

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
        $this->eventDispatcher->dispatch(new BookingStatusEvent($booking));

        return $booking;
    }

    /**
     * @throws BookingAlreadyInFinalStatusException
     * @throws BookingHasExpiredException
     * @throws BookingNotFoundException
     */
    public function update(BookingUpdateRequest $bookingUpdateRequest): void
    {
        $booking = $this->repository->findOneByGoldenId($bookingUpdateRequest->bookingId);

        if ($booking->status === $bookingUpdateRequest->status) {
            return;
        }

        if (BookingStatusConstraint::BOOKING_STATUS_COMPLETE === $bookingUpdateRequest->status &&
            BookingStatusConstraint::BOOKING_STATUS_CANCELLED === $booking->status
        ) {
            throw new BookingAlreadyInFinalStatusException();
        }

        if (BookingStatusConstraint::isAnOnRequestStatus($bookingUpdateRequest->status) &&
            AvailabilityTypeConstraint::AVAILABILITY_TYPE_INSTANT === $booking->availabilityType
        ) {
            throw new UnprocessableEntityException();
        }

        if (BookingStatusConstraint::BOOKING_STATUS_COMPLETE === $bookingUpdateRequest->status &&
            AvailabilityTypeConstraint::AVAILABILITY_TYPE_ON_REQUEST !== $booking->availabilityType &&
            $booking->expiredAt < new \DateTime('now') &&
            $this->isUnavailableForBookings($booking)
        ) {
            throw new BookingHasExpiredException();
        }

        $previousBookingStatus = $booking->status;
        $booking->status = $bookingUpdateRequest->status;

        if (null !== $bookingUpdateRequest->lastStatusChannel &&
            $bookingUpdateRequest->lastStatusChannel !== $booking->lastStatusChannel &&
            BookingChannelConstraint::isValid($bookingUpdateRequest->lastStatusChannel)) {
            $booking->lastStatusChannel = $bookingUpdateRequest->lastStatusChannel;
        }

        if (null !== $bookingUpdateRequest->voucher) {
            $booking->voucher = $bookingUpdateRequest->voucher;
        }

        $this->em->persist($booking);
        $this->em->flush();
        $this->eventDispatcher->dispatch(new BookingStatusEvent($booking, $previousBookingStatus));
    }

    public function import(BookingImportRequest $bookingImportRequest): Booking
    {
        try {
            $this->repository->findOneByGoldenId($bookingImportRequest->bookingId);

            throw new ResourceConflictException();
        } catch (BookingNotFoundException $exception) {
        }

        $experience = $this->experienceRepository->findOneByGoldenId($bookingImportRequest->experience->id);
        $box = $this->boxRepository->findOneByGoldenId($bookingImportRequest->box);

        if (empty($box->brand)) {
            throw new InvalidBoxBrandException();
        }

        if (empty($box->country)) {
            throw new InvalidBoxCountryException();
        }

        $component = $this->componentRepository->findAnyRoomByExperience($experience);

        $partner = $experience->partner;

        $booking = new Booking();
        $booking->partner = $partner;
        $booking->experience = $experience;
        $booking->goldenId = $bookingImportRequest->bookingId;
        $booking->partnerGoldenId = $partner->goldenId;
        $booking->experienceGoldenId = $bookingImportRequest->experience->id;
        $booking->voucher = $bookingImportRequest->voucher;
        $booking->brand = $box->brand;
        $booking->country = $box->country;
        $booking->startDate = $bookingImportRequest->startDate;
        $booking->endDate = $bookingImportRequest->endDate;
        $booking->status = BookingStatusConstraint::BOOKING_STATUS_COMPLETE;
        $booking->customerComment = $bookingImportRequest->customerComment;
        $booking->components = $bookingImportRequest->experience->components;
        $booking->currency = strtoupper($bookingImportRequest->currency);
        $booking->cancelledAt = null;
        $booking->expiredAt = (new \DateTime('now'))->add(new \DateInterval(self::EXPIRATION_TIME));
        $totalPrice = 0;

        /** @var ArrayCollection<int, BookingDate> */
        $bookingDatesCollection = new ArrayCollection();
        foreach ($bookingImportRequest->rooms as $roomIndex => $room) {
            foreach ($room->dates as $date) {
                $totalPrice += $date->price;
                $bookingDate = new BookingDate();
                $bookingDate->booking = $booking;
                $bookingDate->bookingGoldenId = $booking->goldenId;
                $bookingDate->component = $component;
                $bookingDate->componentGoldenId = $component->goldenId;
                $bookingDate->date = $date->day;
                $bookingDate->price = $date->price;
                $bookingDate->guestsCount = $experience->peopleNumber ?? 1; //should we?
                $bookingDate->isExtraRoom = $room->extraRoom;
                $bookingDate->isExtraNight = $date->extraNight;

                $bookingDatesCollection->add($bookingDate);
                $this->em->persist($bookingDate);
            }
        }

        $booking->bookingDate = $bookingDatesCollection;
        $booking->totalPrice = $totalPrice;

        /** @var ArrayCollection<int, Guest> */
        $guestCollection = new ArrayCollection();
        foreach ($bookingImportRequest->guests as $guestRequest) {
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

    protected function isValidAvailabilityType(string $availabilityType, Component $component): bool
    {
        if (!AvailabilityTypeConstraint::isValid($availabilityType)) {
            return false;
        }

        return (AvailabilityTypeConstraint::AVAILABILITY_TYPE_INSTANT === $availabilityType &&
                RoomStockTypeConstraint::isInstantType($component->roomStockType)) ||
            (AvailabilityTypeConstraint::AVAILABILITY_TYPE_ON_REQUEST === $availabilityType &&
                RoomStockTypeConstraint::isOnRequestType($component->roomStockType));
    }

    protected function isUnavailableForBookings(Booking $booking): bool
    {
        $availabilities = $this->roomAvailabilityRepository->findBookingAvailabilityByExperienceAndDates(
            $booking->experienceGoldenId,
            $booking->startDate,
            $booking->endDate
        );

        return count($availabilities) < $booking->startDate->diff($booking->endDate)->days;
    }

    protected function hasStopSaleOnRequestBookings(Booking $booking): bool
    {
        $availabilities = $this->roomAvailabilityRepository->findStopSaleOnRequestAvailabilityByExperienceAndDates(
            $booking->experienceGoldenId,
            $booking->startDate,
            $booking->endDate
        );

        return !empty($availabilities);
    }
}
