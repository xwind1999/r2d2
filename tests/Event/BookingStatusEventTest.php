<?php

declare(strict_types=1);

namespace App\Tests\Event;

use App\Constraint\BookingStatusConstraint;
use App\Entity\Booking;
use App\Event\BookingStatusEvent;
use App\Tests\ProphecyTestCase;

/**
 * @coversDefaultClass \App\Event\BookingStatusEvent
 */
class BookingStatusEventTest extends ProphecyTestCase
{
    /**
     * @covers ::__construct
     * @covers ::getBooking
     * @covers ::getPreviousBookingStatus
     */
    public function testEvent(): void
    {
        $booking = $this->prophesize(Booking::class);
        $event = new BookingStatusEvent($booking->reveal(), BookingStatusConstraint::BOOKING_STATUS_COMPLETE);
        $this->assertInstanceOf(Booking::class, $event->getBooking());
        $this->assertEquals('complete', $event->getPreviousBookingStatus());
    }
}
