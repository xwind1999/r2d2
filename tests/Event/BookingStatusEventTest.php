<?php

declare(strict_types=1);

namespace App\Tests\Event;

use App\Entity\Booking;
use App\Event\BookingStatusEvent;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \App\Event\BookingStatusEvent
 */
class BookingStatusEventTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::getBooking
     */
    public function testEvent(): void
    {
        $booking = $this->prophesize(Booking::class);
        $event = new BookingStatusEvent($booking->reveal());
        $this->assertInstanceOf(Booking::class, $event->getBooking());
    }
}
