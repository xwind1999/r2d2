<?php

declare(strict_types=1);

namespace App\Tests\EventSubscriber;

use App\DBAL\BookingStatus;
use App\Entity\Booking;
use App\Event\BookingStatusEvent;
use App\EventSubscriber\BookingStatusSubscriber;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;

/**
 * @coversDefaultClass \App\EventSubscriber\BookingStatusSubscriber
 */
class BookingStatusSubscriberTest extends TestCase
{
    /**
     * @var LoggerInterface|ObjectProphecy
     */
    private $logger;

    /**
     * @var Booking|ObjectProphecy
     */
    private $booking;

    /**
     * @var BookingStatusEvent|ObjectProphecy
     */
    private $bookingUpdatedEvent;

    private BookingStatusSubscriber $bookingUpdatedSubscriber;

    public function setUp(): void
    {
        $this->logger = $this->prophesize(LoggerInterface::class);
        $this->booking = $this->prophesize(Booking::class);
        $this->bookingUpdatedEvent = $this->prophesize(BookingStatusEvent::class);
        $this->bookingUpdatedSubscriber = new BookingStatusSubscriber($this->logger->reveal());
    }

    /**
     * @covers ::getSubscribedEvents
     */
    public function testGetSubscribedEvents(): void
    {
        $this->assertEquals(
            [
                BookingStatusEvent::class => ['handleBookingStatus'],
            ],
            BookingStatusSubscriber::getSubscribedEvents()
        );
    }

    /**
     * @covers ::__construct
     * @covers ::handleBookingStatus
     *
     * @dataProvider BookingStatusProvider
     */
    public function testHandleMessageByBookingStatus(string $bookingStatus, string $bookingEventLogMessage): void
    {
        $this->booking->status = $bookingStatus;
        $this->logger->info($bookingEventLogMessage, ['booking' => $this->booking])->shouldBeCalledOnce();
        $this->bookingUpdatedEvent->getBooking()->willReturn($this->booking->reveal());
        $this->assertEmpty($this->bookingUpdatedSubscriber->handleBookingStatus($this->bookingUpdatedEvent->reveal()));
    }

    public function testHandleMessageByExpiredAt(): void
    {
        $this->booking->status = 'created';
        $this->booking->expiresAt = new \DateTime('yesterday');

        $this->logger->info(
            BookingStatusEvent::LOG_MESSAGE_BOOKING_STATUS_EXPIRED,
            ['booking' => $this->booking]
        )->shouldBeCalledOnce();
        $this->bookingUpdatedEvent->getBooking()->willReturn($this->booking->reveal());
        $this->assertEmpty($this->bookingUpdatedSubscriber->handleBookingStatus($this->bookingUpdatedEvent->reveal()));
    }

    /**
     * @see testHandleMessageSuccessfully
     */
    public function BookingStatusProvider()
    {
        return [
            [
                'booking with status created' => BookingStatus::BOOKING_STATUS_CREATED,
                    BookingStatusEvent::LOG_MESSAGE_BOOKING_STATUS_CREATED,
            ],
            [
                'booking with status completed' => BookingStatus::BOOKING_STATUS_COMPLETE,
                    BookingStatusEvent::LOG_MESSAGE_BOOKING_STATUS_COMPLETED,
            ],
            [
                'booking with status cancelled' => BookingStatus::BOOKING_STATUS_CANCELLED,
                    BookingStatusEvent::LOG_MESSAGE_BOOKING_STATUS_CANCELLED,
            ],
        ];
    }
}
