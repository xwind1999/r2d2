<?php

declare(strict_types=1);

namespace App\Tests\EventSubscriber;

use App\Constraint\BookingStatusConstraint;
use App\Entity\Booking;
use App\Entity\BookingDate;
use App\Event\BookingStatusEvent;
use App\EventSubscriber\BookingAvailabilitySubscriber;
use App\Exception\Repository\ExperienceNotFoundException;
use App\Manager\RoomAvailabilityManager;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;

/**
 * @coversDefaultClass \App\EventSubscriber\BookingAvailabilitySubscriber
 * @group booking-availability
 */
class BookingAvailabilitySubscriberTest extends TestCase
{
    /**
     * @var ObjectProphecy | RoomAvailabilityManager
     */
    private ObjectProphecy $roomAvailabilityManager;

    /**
     * @var ObjectProphecy | LoggerInterface
     */
    private ObjectProphecy $logger;

    private BookingAvailabilitySubscriber $bookingAvailabilitySubscriber;

    protected function setUp(): void
    {
        $this->logger = $this->prophesize(LoggerInterface::class);
        $this->roomAvailabilityManager = $this->prophesize(RoomAvailabilityManager::class);
        $this->bookingAvailabilitySubscriber = new BookingAvailabilitySubscriber(
            $this->roomAvailabilityManager->reveal(),
            $this->logger->reveal(),
        );
    }

    /**
     * @covers ::getSubscribedEvents
     */
    public function testGetSubscribedEvents(): void
    {
        $this->assertEquals(
            [
                BookingStatusEvent::class => ['handleBookingAvailability'],
            ],
            BookingAvailabilitySubscriber::getSubscribedEvents()
        );
    }

    /**
     * @dataProvider bookingProvider
     */
    public function testHandleBookingAvailability(BookingStatusEvent $bookingEvent, callable $prophecies)
    {
        $prophecies($this);
        $this->bookingAvailabilitySubscriber->handleBookingAvailability($bookingEvent);
    }

    public function bookingProvider()
    {
        $booking = new Booking();
        $booking->goldenId = '12345';
        $dateTime = new \DateTime('2020-10-01');
        $booking->startDate = $dateTime;
        $booking->endDate = (new $dateTime())->modify('+1 day');
        $booking->expiredAt = (new $dateTime())->modify('+15 minutes');
        $booking->experienceGoldenId = '1234154';

        $bookingDate = $this->prophesize(BookingDate::class);
        $bookingDate->componentGoldenId = '5464';
        $bookingDate->date = $dateTime;
        $bookingDate->price = 1212;
        $booking->bookingDate = new ArrayCollection([$bookingDate->reveal()]);

        $bookingStatusEvent = $this->prophesize(BookingStatusEvent::class);
        $bookingStatusEvent->getBooking()->willReturn($booking);

        yield 'booking-confirmation-success' => [
            (function ($booking, $bookingStatusEvent) {
                $booking->status = BookingStatusConstraint::BOOKING_STATUS_COMPLETE;
                $bookingStatusEvent->getBooking()->willReturn($booking);

                return $bookingStatusEvent->reveal();
            })(new $booking(), $bookingStatusEvent),
            (function ($test) {
                $test->roomAvailabilityManager->updateStockBookingConfirmation(Argument::type(Booking::class))->shouldBeCalledOnce();
            }),
        ];

        yield 'booking-confirmation-error' => [
            (function ($booking, $bookingStatusEvent) {
                $booking->status = BookingStatusConstraint::BOOKING_STATUS_COMPLETE;
                $bookingStatusEvent->getBooking()->willReturn($booking);

                return $bookingStatusEvent->reveal();
            })(new $booking(), $bookingStatusEvent),
            (function ($test) {
                $test->roomAvailabilityManager
                    ->updateStockBookingConfirmation(Argument::type(Booking::class))
                    ->willThrow(ExperienceNotFoundException::class);
                $test->roomAvailabilityManager
                    ->updateStockBookingConfirmation(Argument::type(Booking::class))
                    ->shouldBeCalledOnce();
                $test->logger->warning(Argument::any(), Argument::any())->shouldBeCalled();
            }),
        ];
    }
}
