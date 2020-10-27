<?php

declare(strict_types=1);

namespace App\Contract\Request\EAI;

use App\Constraint\CMHStatusConstraint;
use App\Entity\Booking;
use App\Entity\BookingDate;
use App\Event\NamedEventInterface;
use Clogger\ContextualInterface;
use Smartbox\CDM\Entity\Booking\ChannelManagerBooking;
use Smartbox\CDM\Entity\Booking\Guest;
use Smartbox\CDM\Entity\Common\Country;
use Smartbox\CDM\Entity\Common\Price;
use Smartbox\CDM\Entity\Component\Component;
use Smartbox\CDM\Entity\Partner\Partner;
use Smartbox\CDM\Entity\Product\Experience;
use Smartbox\CDM\Entity\Product\Product;
use Smartbox\CDM\Entity\Product\RoomTypeProduct;
use Smartbox\CDM\Entity\Room\DailyRate;
use Smartbox\CDM\Entity\Room\Room;
use Smartbox\CDM\Entity\Voucher\Voucher;

class ChannelManagerBookingRequest extends ChannelManagerBooking implements ContextualInterface, NamedEventInterface
{
    private const EVENT_NAME = '%s booking pushed to EAI';
    private const DEFAULT_AGE = 25; // age is hardcoded

    public static function fromCompletedBooking(Booking $booking): self
    {
        return self::createChannelManagerBookingRequest($booking, CMHStatusConstraint::BOOKING_STATUS_CONFIRMED);
    }

    public static function fromCancelledBooking(Booking $booking): self
    {
        return self::createChannelManagerBookingRequest($booking, CMHStatusConstraint::BOOKING_STATUS_CANCELLED);
    }

    public function getContext(): array
    {
        return [
            'booking_golden_id' => $this->getId(),
            'booking_status' => $this->getStatus(),
            'booking_total_amount' => $this->getTotalPrice()->getAmount(),
            'booking_currency_code' => $this->getTotalPrice()->getCurrencyCode(),
            'booking_start_date' => $this->getStartDate(),
            'booking_end_date' => $this->getEndDate(),
            'booking_created_at' => $this->getCreatedAt(),
            'booking_updated_at' => $this->getUpdatedAt(),
            'voucher_golden_id' => $this->getVoucher()->getId(),
            'partner_golden_id' => $this->getPartner()->getId(),
            'experience_golden_id' => $this->getExperience()->getId(),
            'experience_total_price' => $this->getExperience()->getPrice()->getAmount(),
            'experience_currency' => $this->getExperience()->getPrice()->getCurrencyCode(),
        ];
    }

    private static function createChannelManagerBookingRequest(Booking $booking, string $bookingStatus): self
    {
        $bookingRequest = new self();
        $bookingRequest->setId($booking->goldenId);
        $bookingRequest->setStatus($bookingStatus);
        $bookingRequest->setStartDate($booking->startDate);
        $bookingRequest->setEndDate($booking->endDate);
        $bookingRequest->setCreatedAt($booking->createdAt);
        $bookingRequest->setUpdatedAt($booking->updatedAt);

        $voucher = new Voucher();
        if (!empty($booking->voucher)) {
            $voucher->setId($booking->voucher);
        }
        $bookingRequest->setVoucher($voucher);

        $partner = new Partner();
        $partner->setId($booking->partnerGoldenId);
        $bookingRequest->setPartner($partner);

        $experience = new Experience();
        $experience->setId($booking->experienceGoldenId);

        $experiencePrice = new Price();
        if (!empty($booking->experience->price)) {
            $experiencePrice->setAmount($booking->experience->price);
        }
        $experiencePrice->setCurrencyCode($booking->experience->currency ?: $booking->currency);
        $experience->setPrice($experiencePrice);

        $bookingPrice = new Price();
        $bookingPrice->setAmount($booking->totalPrice);
        $bookingPrice->setCurrencyCode($booking->currency);
        $bookingRequest->setTotalPrice($bookingPrice);

        $componentsArray = [];
        foreach ($booking->components as $component) {
            $componentObject = new Component();
            $componentObject->setName($component);
            $componentsArray[] = $componentObject;
        }
        $experience->setComponents($componentsArray);
        $bookingRequest->setExperience($experience);

        $country = new Country();
        $country->setCode($booking->country);

        $guestArray = [];
        $loopIdentifier = 0;
        $mainGuest = new Guest();
        foreach ($booking->guest->getIterator() as $guest) {
            $guestObject = new Guest();
            $guestObject->setFirstName($guest->firstName ?: $mainGuest->getFirstName());
            $guestObject->setLastName($guest->lastName ?: $mainGuest->getLastName());
            $guestObject->setEmailAddress($guest->email ?: $mainGuest->getEmailAddress());
            $guestObject->setPhoneNumber($guest->phone ?: $mainGuest->getPhoneNumber());
            $guestObject->setIsPrimary(0 === $loopIdentifier);
            $guestObject->setAge(self::DEFAULT_AGE);
            $guestObject->setCountry($country);
            $guestArray[] = $guestObject;

            if (0 === $loopIdentifier) {
                $mainGuest = clone $guestObject;
                ++$loopIdentifier;
            }
        }

        $extraRoomAndNightStatus = false;
        $extraRoomStatus = false;
        $dailyRateArray = [];
        $roomsArray = [];
        $roomsArrayIndex = 0;

        $room = new Room();
        $product = new Product();
        $roomTypeProduct = new RoomTypeProduct();

        /**
         * @var BookingDate $bookingDate
         */
        foreach ($booking->bookingDate->getIterator() as $index => $bookingDate) {
            if (true === $bookingDate->isExtraRoom &&
                true === $bookingDate->isExtraNight &&
                false === $extraRoomAndNightStatus
            ) {
                $room = new Room();
                $product->setId($bookingDate->componentGoldenId);
                $product->setName($bookingDate->component->name);
                $roomTypeProduct->setProduct($product);

                $room->setRoomTypeProduct($roomTypeProduct);
                $room->setGuests($guestArray);
                $extraRoomAndNightStatus = true;
                $roomsArrayIndex = 0;
                $dailyRateArray = null;
            } elseif (false === $bookingDate->isExtraRoom && false === $extraRoomStatus) {
                $room = new Room();
                $product->setId($bookingDate->componentGoldenId);
                $product->setName($bookingDate->component->name);
                $roomTypeProduct->setProduct($product);

                $room->setRoomTypeProduct($roomTypeProduct);
                $room->setGuests($guestArray);

                $extraRoomStatus = true;
                $roomsArrayIndex = 1;
                $dailyRateArray = null;
            }

            $dailyRate = new DailyRate();
            $dailyRate->setDate($bookingDate->date);
            $ratePrice = new Price();
            $ratePrice->setAmount($bookingDate->price);
            $ratePrice->setCurrencyCode($booking->currency);
            $dailyRate->setRate($ratePrice);
            $dailyRateArray[] = $dailyRate;
            $room->setDailyRates($dailyRateArray);

            $roomsArray[$roomsArrayIndex] = $room;
        }

        $bookingRequest->setRooms($roomsArray);

        return $bookingRequest;
    }

    public function getEventName(): string
    {
        return sprintf(static::EVENT_NAME, $this->getStatus());
    }
}
