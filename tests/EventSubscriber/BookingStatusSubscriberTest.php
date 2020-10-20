<?php

declare(strict_types=1);

namespace App\Tests\EventSubscriber;

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
use App\Event\BookingStatusEvent;
use App\EventSubscriber\BookingStatusSubscriber;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

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
     * @var BookingStatusEvent|ObjectProphecy
     */
    private $bookingUpdatedEvent;

    /**
     * @var MessageBusInterface|ObjectProphecy
     */
    private $messageBus;

    private BookingStatusSubscriber $bookingUpdatedSubscriber;
    private Booking $booking;

    public function setUp(): void
    {
        $this->logger = $this->prophesize(LoggerInterface::class);
        $this->bookingUpdatedEvent = $this->prophesize(BookingStatusEvent::class);
        $this->messageBus = $this->prophesize(MessageBusInterface::class);
        $this->bookingUpdatedSubscriber = new BookingStatusSubscriber(
            $this->logger->reveal(),
            $this->messageBus->reveal(),
        );
        $this->booking = new Booking();
        $this->booking->voucher = '198257918';
        $this->booking->goldenId = '12345';
        $dateTime = new \DateTime('2020-08-05 16:58:11.455209');
        $this->booking->startDate = $dateTime;
        $this->booking->endDate = $dateTime;
        $this->booking->createdAt = $dateTime;
        $this->booking->updatedAt = $dateTime;
        $this->booking->voucher = '1234154';
        $this->booking->partnerGoldenId = '1234154';
        $this->booking->country = 'FR';
        $this->booking->totalPrice = 1212;
        $this->booking->experienceGoldenId = '1234154';
        $this->booking->components = [
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
        $this->booking->experience = $experience->reveal();

        $partner = $this->prophesize(Partner::class);
        $partner->currency = 'EUR';
        $this->booking->partner = $partner->reveal();

        $bookingDate = $this->prophesize(BookingDate::class);
        $bookingDate->componentGoldenId = '5464';
        $bookingDate->component = $component;
        $bookingDate->date = $dateTime;
        $bookingDate->price = 1212;
        $this->booking->bookingDate = new ArrayCollection([$bookingDate->reveal()]);

        $guest = $this->prophesize(Guest::class);
        $guest->firstName = 'First Name';
        $guest->lastName = 'Last Name';
        $guest->phone = '089 585 5555';
        $guest->email = 'teste@teste.com';
        $this->booking->guest = new ArrayCollection([$guest->reveal()]);
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
     * @covers ::processLogMessage
     */
    public function testHandleCreatedMessage(): void
    {
        $this->booking->status = BookingStatusConstraint::BOOKING_STATUS_CREATED;
        $this->booking->createdAt = new \DateTime();
        $this->logger
            ->info(BookingStatusEvent::LOG_MESSAGE_BOOKING_STATUS_CREATED, ['booking' => $this->booking])
            ->shouldBeCalledOnce()
        ;
        $this->bookingUpdatedEvent->getBooking()->willReturn($this->booking);
        $this->messageBus->dispatch(Argument::any())->shouldNotBeCalled();
        $this->bookingUpdatedSubscriber->handleBookingStatus($this->bookingUpdatedEvent->reveal());
    }

    /**
     * @covers ::__construct
     * @covers ::handleBookingStatus
     * @covers ::processLogMessage
     * @covers \App\Contract\Request\EAI\ChannelManagerBookingRequest::fromCompletedBooking
     * @covers \App\Contract\Request\EAI\ChannelManagerBookingRequest::createChannelManagerBookingRequest
     */
    public function testHandleCompletedMessage(): void
    {
        $this->booking->status = BookingStatusConstraint::BOOKING_STATUS_COMPLETE;
        $this->booking->goldenId = '12345';
        $this->booking->startDate = new \DateTime();
        $this->booking->endDate = new \DateTime();
        $this->booking->createdAt = new \DateTime();
        $this->booking->currency = 'EUR';
        $this->logger
            ->info(
                BookingStatusEvent::LOG_MESSAGE_BOOKING_STATUS_COMPLETED,
                ['booking' => $this->booking]
            )
            ->shouldBeCalledOnce()
        ;
        $this->bookingUpdatedEvent->getBooking()->willReturn($this->booking);
        $this->messageBus->dispatch(Argument::any())->shouldBeCalledOnce()->willReturn(new Envelope(new \stdClass()));
        $this->bookingUpdatedSubscriber->handleBookingStatus($this->bookingUpdatedEvent->reveal());
    }

    /**
     * @covers ::__construct
     * @covers ::handleBookingStatus
     * @covers ::processLogMessage
     * @covers \App\Contract\Request\EAI\ChannelManagerBookingRequest::fromCancelledBooking
     * @covers \App\Contract\Request\EAI\ChannelManagerBookingRequest::createChannelManagerBookingRequest
     */
    public function testHandleCancelledMessages(): void
    {
        $this->booking->status = BookingStatusConstraint::BOOKING_STATUS_CANCELLED;
        $this->booking->currency = 'EUR';
        $this->booking->createdAt = new \DateTime();
        $this->logger
            ->info(BookingStatusEvent::LOG_MESSAGE_BOOKING_STATUS_CANCELLED, ['booking' => $this->booking])
            ->shouldBeCalledOnce()
        ;
        $this->bookingUpdatedEvent->getBooking()->willReturn($this->booking);
        $this->bookingUpdatedEvent
            ->getPreviousBookingStatus()
            ->willReturn(BookingStatusConstraint::BOOKING_STATUS_COMPLETE)
        ;
        $this->messageBus->dispatch(Argument::any())->shouldBeCalledOnce()->willReturn(new Envelope(new \stdClass()));
        $this->bookingUpdatedSubscriber->handleBookingStatus($this->bookingUpdatedEvent->reveal());
    }

    /**
     * @covers ::__construct
     * @covers ::handleBookingStatus
     * @covers ::processLogMessage
     */
    public function testHandleMessageByExpiredAt(): void
    {
        $this->booking->status = 'created';
        $this->booking->expiredAt = new \DateTime('yesterday');
        $this->logger
            ->info(
                BookingStatusEvent::LOG_MESSAGE_BOOKING_STATUS_EXPIRED,
                ['booking' => $this->booking]
            )
            ->shouldBeCalledOnce()
        ;
        $this->bookingUpdatedEvent->getBooking()->willReturn($this->booking);
        $this->messageBus->dispatch(Argument::any())->shouldNotBeCalled();
        $this->bookingUpdatedSubscriber->handleBookingStatus($this->bookingUpdatedEvent->reveal());
    }
}
