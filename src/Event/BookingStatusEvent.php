<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\Booking;
use Symfony\Contracts\EventDispatcher\Event;

class BookingStatusEvent extends Event
{
    public const LOG_MESSAGE_BOOKING_STATUS_CREATED = 'Booking has been created';
    public const LOG_MESSAGE_BOOKING_STATUS_COMPLETED = 'Booking has been completed';
    public const LOG_MESSAGE_BOOKING_STATUS_CANCELLED = 'Booking has been cancelled';
    public const LOG_MESSAGE_BOOKING_STATUS_EXPIRED = 'Booking has been expired';

    private Booking $booking;
    private ?string $previousBookingStatus;

    public function __construct(Booking $booking, string $previousBooking = null)
    {
        $this->booking = $booking;
        $this->previousBookingStatus = $previousBooking;
    }

    public function getBooking(): Booking
    {
        return $this->booking;
    }

    public function getPreviousBookingStatus(): ?string
    {
        return $this->previousBookingStatus;
    }
}
