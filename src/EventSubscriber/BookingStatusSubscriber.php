<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Constraint\BookingStatusConstraint;
use App\Contract\Request\EAI\ChannelManagerBookingRequest;
use App\Entity\Booking;
use App\Event\BookingStatusEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class BookingStatusSubscriber implements EventSubscriberInterface
{
    private LoggerInterface $logger;
    private MessageBusInterface $messageBus;

    public function __construct(LoggerInterface $logger, MessageBusInterface $messageBus)
    {
        $this->logger = $logger;
        $this->messageBus = $messageBus;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            BookingStatusEvent::class => ['handleBookingStatus'],
        ];
    }

    public function handleBookingStatus(BookingStatusEvent $event): void
    {
        $booking = $event->getBooking();
        if (!empty($booking->expiredAt) && $booking->expiredAt < new \DateTime('now')) {
            $this->processLogMessage(BookingStatusEvent::LOG_MESSAGE_BOOKING_STATUS_EXPIRED, $booking);
        } elseif (BookingStatusConstraint::BOOKING_STATUS_CREATED === $booking->status) {
            $this->processLogMessage(BookingStatusEvent::LOG_MESSAGE_BOOKING_STATUS_CREATED, $booking);
        } elseif (BookingStatusConstraint::BOOKING_STATUS_COMPLETE === $booking->status) {
            $this->processLogMessage(BookingStatusEvent::LOG_MESSAGE_BOOKING_STATUS_COMPLETED, $booking);
            $this->messageBus->dispatch(ChannelManagerBookingRequest::fromCompletedBooking($booking));
        } elseif (BookingStatusConstraint::BOOKING_STATUS_CANCELLED === $booking->status) {
            $this->processLogMessage(BookingStatusEvent::LOG_MESSAGE_BOOKING_STATUS_CANCELLED, $booking);
            $this->messageBus->dispatch(ChannelManagerBookingRequest::fromCancelledBooking($booking));
        }
    }

    private function processLogMessage(string $message, Booking $booking): void
    {
        $this->logger->info($message, ['booking' => $booking]);
    }
}
