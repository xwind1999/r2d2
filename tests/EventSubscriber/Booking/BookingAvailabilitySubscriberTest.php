<?php

declare(strict_types=1);

namespace App\Tests\EventSubscriber\Booking;

use App\Constraint\BookingStatusConstraint;
use App\Entity\Booking;
use App\Entity\BookingDate;
use App\Event\Booking\BookingStatusEvent;
use App\EventSubscriber\Booking\BookingAvailabilitySubscriber;
use App\Exception\Repository\ExperienceNotFoundException;
use App\Manager\RoomAvailabilityManager;
use App\Tests\ProphecyTestCase;
use Doctrine\Common\Collections\ArrayCollection;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;

/**
 * @coversDefaultClass \App\EventSubscriber\Booking\BookingAvailabilitySubscriber
 * @group booking-availability
 */
class BookingAvailabilitySubscriberTest extends ProphecyTestCase
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

        yield 'booking confirmation' => [
            (function ($booking) {
                $booking->status = BookingStatusConstraint::BOOKING_STATUS_COMPLETE;

                return new BookingStatusEvent($booking, BookingStatusConstraint::BOOKING_STATUS_CREATED);
            })(clone $booking),
            (function ($test) {
                $test
                    ->roomAvailabilityManager
                    ->updateStockBookingConfirmation(Argument::type(Booking::class))
                    ->shouldBeCalledOnce();
            }),
        ];

        yield 'booking cancellation with previous status=created' => [
            (function ($booking) {
                $booking->status = BookingStatusConstraint::BOOKING_STATUS_CANCELLED;

                return new BookingStatusEvent($booking, BookingStatusConstraint::BOOKING_STATUS_CREATED);
            })(clone $booking),
            (function ($test) {
                $test
                    ->roomAvailabilityManager
                    ->updateStockBookingCancellation(Argument::type(Booking::class))
                    ->shouldNotBeCalled();
            }),
        ];

        yield 'booking cancellation with previous status=complete' => [
            (function ($booking) {
                $booking->status = BookingStatusConstraint::BOOKING_STATUS_CANCELLED;

                return new BookingStatusEvent($booking, BookingStatusConstraint::BOOKING_STATUS_COMPLETE);
            })(clone $booking),
            (function ($test) {
                $test
                    ->roomAvailabilityManager
                    ->updateStockBookingCancellation(Argument::type(Booking::class))
                    ->shouldBeCalled();
            }),
        ];

        yield 'booking-confirmation-error' => [
            (function ($booking) {
                $booking->status = BookingStatusConstraint::BOOKING_STATUS_COMPLETE;

                return new BookingStatusEvent($booking, BookingStatusConstraint::BOOKING_STATUS_CREATED);
            })(clone $booking),
            (function ($test) {
                $test->roomAvailabilityManager
                    ->updateStockBookingConfirmation(Argument::type(Booking::class))
                    ->shouldBeCalledOnce()
                    ->willThrow(ExperienceNotFoundException::class);
                $test->logger->warning(Argument::type('string'), Argument::type('array'))->shouldBeCalled();
            }),
        ];
    }
}
