<?php

declare(strict_types=1);

namespace App\Contract\Request\EAI;

use App\Constraint\CMHStatusConstraint;
use App\Entity\Booking;
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

class ChannelManagerBookingRequest extends ChannelManagerBooking
{
    private const DEFAULT_AGE = 25; // age is hardcoded

    public static function fromCompletedBooking(Booking $booking): self
    {
        return self::createChannelManagerBookingRequest($booking, CMHStatusConstraint::BOOKING_STATUS_CONFIRMED);
    }

    public static function fromCancelledBooking(Booking $booking): self
    {
        return self::createChannelManagerBookingRequest($booking, CMHStatusConstraint::BOOKING_STATUS_CANCELLED);
    }

    /**
     * @codeCoverageIgnore
     */
    public function getContext(): array
    {
        return [
            'booking_golden_id' => $this->getId(),
            'booking_status' => $this->getStatus(),
            'booking_start_date' => $this->getStartDate(),
            'booking_end_date' => $this->getEndDate(),
            'booking_created_at' => $this->getCreatedAt(),
            'booking_updated_at' => $this->getUpdatedAt(),
            'voucher_golden_id' => $this->getVoucher()->getId() ?: '',
            'partner_golden_id' => $this->getPartner()->getId(),
            'price_amount' => $this->getTotalPrice()->getAmount(),
            'price_currency_code' => $this->getTotalPrice()->getCurrencyCode(),
            'experience_golden_id' => $this->getExperience()->getId(),
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

        $price = new Price();
        if (!empty($booking->experience->price)) {
            $price->setAmount($booking->experience->price);
        }
        $price->setCurrencyCode($booking->currency);
        $bookingRequest->setTotalPrice($price);

        $experience = new Experience();
        $experience->setId($booking->experienceGoldenId);
        $experience->setPrice($price);
        $componentsArray = [];
        foreach ($booking->components as $component) {
            $componentObject = new Component();
            $componentObject->setName($component);
            $componentsArray[] = $componentObject;
        }
        $experience->setComponents($componentsArray);
        $bookingRequest->setExperience($experience);

        // Generate array of components with its IDs and names to be used during the next loop
        $components = [];
        foreach ($booking->experience->experienceComponent->getIterator() as $experienceComponent) {
            $components[$experienceComponent->component->goldenId] = $experienceComponent->component->name;
        }

        $country = new Country();
        foreach ($booking->experience->boxExperience->getIterator() as $boxExperience) {
            if ($boxExperience->box->country) {
                $country->setCode($boxExperience->box->country);
                break;
            }
        }

        $roomArray = [];
        $dailyRateArray = [];
        $guestArray = [];
        $room = new Room();
        $previousComponentGoldenId = null;
        $previousComponentGoldenIdIndex = 0;
        foreach ($booking->bookingDate->getIterator() as $index => $bookingDate) {
            if ($previousComponentGoldenId !== $bookingDate->componentGoldenId) {
                $roomTypeProduct = new RoomTypeProduct();
                $product = new Product();
                $product->setId($bookingDate->componentGoldenId);
                $product->setName($components[$bookingDate->componentGoldenId]);
                $roomTypeProduct->setProduct($product);
                $room->setRoomTypeProduct($roomTypeProduct);
                $previousComponentGoldenId = $bookingDate->componentGoldenId;
                $previousComponentGoldenIdIndex = $index;

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
                $room->setGuests($guestArray);
                $guestArray = [];
            }

            $dailyRate = new DailyRate();
            $dailyRate->setDate($bookingDate->date);
            $ratePrice = new Price();
            $ratePrice->setAmount($bookingDate->price);
            $ratePrice->setCurrencyCode($booking->currency);
            $dailyRate->setRate($price);
            $dailyRateArray[] = $dailyRate;
            $room->setDailyRates($dailyRateArray);

            if ($previousComponentGoldenId === $bookingDate->componentGoldenId) {
                $roomArray[$previousComponentGoldenIdIndex] = $room;
            }
        }

        $bookingRequest->setRooms($roomArray);

        return $bookingRequest;
    }
}
