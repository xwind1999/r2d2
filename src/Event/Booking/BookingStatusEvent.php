<?php

declare(strict_types=1);

namespace App\Event\Booking;

use App\Entity\Booking;
use Symfony\Contracts\EventDispatcher\Event;

class BookingStatusEvent extends Event
{
    public const CREATED_BOOKING_LOG_MESSAGE = 'Booking has been created';
    public const COMPLETED_BOOKING_LOG_MESSAGE = 'Booking has been completed';
    public const CANCELLED_BOOKING_LOG_MESSAGE = 'Booking has been cancelled';
    public const EXPIRED_BOOKING_LOG_MESSAGE = 'Booking has been expired';
    public const REJECTED_BOOKING_LOG_MESSAGE = 'Booking has been rejected';
    public const PENDING_PARTNER_CONFIRMATION_BOOKING_LOG_MESSAGE =
        'Booking has been set to pending partner confirmation';

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
