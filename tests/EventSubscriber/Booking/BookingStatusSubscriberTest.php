<?php

declare(strict_types=1);

namespace App\Tests\EventSubscriber\Booking;

use App\Constraint\BookingStatusConstraint;
use App\Entity\Booking;
use App\Entity\BookingDate;
use App\Entity\Box;
use App\Entity\BoxExperience;
use App\Entity\Component;
use App\Entity\Experience;
use App\Entity\ExperienceComponent;
use App\Entity\Guest;
use App\Entity\Partner;
use App\Event\Booking\BookingStatusEvent;
use App\EventSubscriber\Booking\BookingStatusSubscriber;
use App\Tests\ProphecyTestCase;
use Doctrine\Common\Collections\ArrayCollection;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @coversDefaultClass \App\EventSubscriber\Booking\BookingStatusSubscriber
 */
class BookingStatusSubscriberTest extends ProphecyTestCase
{
    /**
     * @var LoggerInterface
     */
    private ObjectProphecy $logger;

    /**
     * @var BookingStatusEvent
     */
    private ObjectProphecy $bookingUpdatedEvent;

    /**
     * @var MessageBusInterface
     */
    private ObjectProphecy $messageBus;

    private BookingStatusSubscriber $bookingUpdatedSubscriber;

    public function setUp(): void
    {
        $this->logger = $this->prophesize(LoggerInterface::class);
        $this->bookingUpdatedEvent = $this->prophesize(BookingStatusEvent::class);
        $this->messageBus = $this->prophesize(MessageBusInterface::class);
        $this->bookingUpdatedSubscriber = new BookingStatusSubscriber(
            $this->logger->reveal(),
            $this->messageBus->reveal(),
        );
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
     * @covers \App\Contract\Request\EAI\ChannelManagerBookingRequest::createChannelManagerBookingRequest
     * @dataProvider handleDataProvider
     */
    public function testHandle(Booking $booking, callable $asserts): void
    {
        $asserts($this, $booking);
    }

    /**
     * @see testHandle
     */
    public function handleDataProvider(): \Generator
    {
        $booking = new Booking();
        $booking->voucher = '198257918';
        $booking->goldenId = '12345';
        $booking->currency = 'EUR';
        $dateTime = new \DateTime('2020-08-05 16:58:11.455209');
        $booking->startDate = $dateTime;
        $booking->endDate = $dateTime;
        $booking->createdAt = $dateTime;
        $booking->updatedAt = $dateTime;
        $booking->voucher = '1234154';
        $booking->partnerGoldenId = '1234154';
        $booking->country = 'FR';
        $booking->totalPrice = 1212;
        $booking->experienceGoldenId = '1234154';
        $booking->components = [
            'name' => 'name',
        ];

        $experienceComponent = $this->prophesize(ExperienceComponent::class);
        $component = $this->prophesize(Component::class);
        $component->goldenId = '5464';
        $component->name = 'component name';
        $experienceComponent->component = $component->reveal();

        $boxExperience = $this->prophesize(BoxExperience::class);
        $box = $this->prophesize(Box::class);
        $box->country = 'FR';
        $boxExperience->box = $box->reveal();

        $experience = $this->prophesize(Experience::class);
        $experience->price = 125;
        $experience->experienceComponent = new ArrayCollection([$experienceComponent->reveal()]);
        $experience->boxExperience = new ArrayCollection([$boxExperience->reveal()]);
        $booking->experience = $experience->reveal();

        $partner = $this->prophesize(Partner::class);
        $partner->currency = 'EUR';
        $booking->partner = $partner->reveal();

        $bookingDateDayOne = $this->prophesize(BookingDate::class);
        $bookingDateDayOne->componentGoldenId = '5464';
        $bookingDateDayOne->component = $component;
        $bookingDateDayOne->date = $dateTime;
        $bookingDateDayOne->price = 606;
        $bookingDateDayOne->isExtraNight = true;
        $bookingDateDayOne->isExtraRoom = true;

        $booking->bookingDate = new ArrayCollection([$bookingDateDayOne->reveal()]);

        $guest = $this->prophesize(Guest::class);
        $guest->firstName = 'First Name';
        $guest->lastName = 'Last Name';
        $guest->phone = '089 585 5555';
        $guest->email = 'teste@teste.com';
        $booking->guest = new ArrayCollection([$guest->reveal()]);

        yield 'booking-with-created-status' => [
            (function ($booking) {
                $booking->status = BookingStatusConstraint::BOOKING_STATUS_CREATED;
                $booking->createdAt = new \DateTime();

                return $booking;
            })(clone $booking),
            (function ($test, $booking) {
                $test->logger
                    ->info(BookingStatusEvent::CREATED_BOOKING_LOG_MESSAGE, ['booking' => $booking])
                    ->shouldBeCalledOnce()
                ;
                $test->bookingUpdatedEvent->getBooking()->willReturn($booking);
                $test->messageBus->dispatch(Argument::any())->shouldNotBeCalled();
                $test->bookingUpdatedSubscriber->handleBookingStatus($test->bookingUpdatedEvent->reveal());
            }),
        ];

        yield 'booking-with-complete-status' => [
            (function ($booking) {
                $booking->status = 'complete';
                $booking->goldenId = '12345';
                $booking->startDate = new \DateTime();
                $booking->endDate = new \DateTime();
                $booking->createdAt = new \DateTime();
                $booking->currency = 'EUR';

                return $booking;
            })(clone $booking),
            (function ($test, $booking) {
                $test->logger
                    ->info(
                        BookingStatusEvent::COMPLETED_BOOKING_LOG_MESSAGE,
                        ['booking' => $booking]
                    )
                    ->shouldBeCalledOnce()
                ;
                $test->bookingUpdatedEvent->getBooking()->willReturn($booking);
                $test->messageBus->dispatch(Argument::any())->shouldBeCalledOnce()->willReturn(new Envelope(new \stdClass()));
                $test->bookingUpdatedSubscriber->handleBookingStatus($test->bookingUpdatedEvent->reveal());
            }),
        ];

        yield 'booking-with-cancelled-status' => [
            (function ($booking) {
                $booking->status = 'cancelled';
                $booking->currency = 'EUR';
                $booking->createdAt = new \DateTime();

                return $booking;
            })(clone $booking),
            (function ($test, $booking) {
                $test->logger
                    ->info(BookingStatusEvent::CANCELLED_BOOKING_LOG_MESSAGE, ['booking' => $booking])
                    ->shouldBeCalledOnce()
                ;
                $test->bookingUpdatedEvent->getBooking()->willReturn($booking);
                $test->bookingUpdatedEvent
                    ->getPreviousBookingStatus()
                    ->willReturn(BookingStatusConstraint::BOOKING_STATUS_COMPLETE)
                ;
                $test->messageBus->dispatch(Argument::any())->shouldBeCalledOnce()->willReturn(new Envelope(new \stdClass()));
                $test->bookingUpdatedSubscriber->handleBookingStatus($test->bookingUpdatedEvent->reveal());
            }),
        ];

        yield 'booking-with-expired-date' => [
            (function ($booking) {
                $booking->status = 'created';
                $booking->expiredAt = new \DateTime('yesterday');

                return $booking;
            })(clone $booking),
            (function ($test, $booking) {
                $test->logger->info(
                    BookingStatusEvent::EXPIRED_BOOKING_LOG_MESSAGE,
                    ['booking' => $booking]
                )->shouldBeCalledOnce();
                $test->bookingUpdatedEvent->getBooking()->willReturn($booking);
                $test->messageBus->dispatch(Argument::any())->shouldNotBeCalled();
                $test->bookingUpdatedSubscriber->handleBookingStatus($test->bookingUpdatedEvent->reveal());
            }),
        ];

        yield 'booking-with-rejected-status' => [
            (function ($booking) {
                $booking->status = 'rejected';
                $booking->expiredAt = new \DateTime('yesterday');

                return $booking;
            })(clone $booking),
            (function ($test, $booking) {
                $test->logger
                    ->info(BookingStatusEvent::REJECTED_BOOKING_LOG_MESSAGE, ['booking' => $booking])
                    ->shouldBeCalledOnce()
                ;
                $test->bookingUpdatedEvent->getBooking()->willReturn($booking);
                $test->messageBus->dispatch(
                    Argument::any())->shouldBeCalledOnce()->willReturn(new Envelope(new \stdClass())
                );
                $test->bookingUpdatedSubscriber->handleBookingStatus($test->bookingUpdatedEvent->reveal());
            }),
        ];

        yield 'booking-with-pending-partner-confirmation-status' => [
            (function ($booking) {
                $booking->status = 'pending_partner_confirmation';

                return $booking;
            })(clone $booking),
            (function ($test, $booking) {
                $test->logger
                    ->info(
                        BookingStatusEvent::PENDING_PARTNER_CONFIRMATION_BOOKING_LOG_MESSAGE,
                        ['booking' => $booking]
                    )
                    ->shouldBeCalledOnce()
                ;
                $test->bookingUpdatedEvent->getBooking()->willReturn($booking);
                $test->messageBus->dispatch(Argument::any())->shouldNotBeCalled();
                $test->bookingUpdatedSubscriber->handleBookingStatus($test->bookingUpdatedEvent->reveal());
            }),
        ];
    }
}
