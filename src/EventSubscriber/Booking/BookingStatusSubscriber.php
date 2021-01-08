<?php

declare(strict_types=1);

namespace App\EventSubscriber\Booking;

use App\Constraint\BookingStatusConstraint;
use App\Contract\Request\EAI\ChannelManagerBookingRequest;
use App\Event\Booking\BookingStatusEvent;
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
        switch ($booking->status) {
            case BookingStatusConstraint::BOOKING_STATUS_CREATED === $booking->status:
                if (!empty($booking->expiredAt) && $booking->expiredAt < new \DateTime('now')) {
                    $this->logger->info(BookingStatusEvent::EXPIRED_BOOKING_LOG_MESSAGE, ['booking' => $booking]);
                } else {
                    $this->logger->info(BookingStatusEvent::CREATED_BOOKING_LOG_MESSAGE, ['booking' => $booking]);
                }
                break;

            case BookingStatusConstraint::BOOKING_STATUS_COMPLETE:
                $this->logger->info(BookingStatusEvent::COMPLETED_BOOKING_LOG_MESSAGE, ['booking' => $booking]);
                $this->messageBus->dispatch(ChannelManagerBookingRequest::fromCompletedBooking($booking));
                break;

            case BookingStatusConstraint::BOOKING_STATUS_CANCELLED:
                $this->logger->info(BookingStatusEvent::CANCELLED_BOOKING_LOG_MESSAGE, ['booking' => $booking]);

                if (BookingStatusConstraint::BOOKING_STATUS_COMPLETE === $event->getPreviousBookingStatus()) {
                    $this->messageBus->dispatch(ChannelManagerBookingRequest::fromCancelledBooking($booking));
                }
                break;

            case BookingStatusConstraint::BOOKING_STATUS_REJECTED:
                $this->logger->info(BookingStatusEvent::REJECTED_BOOKING_LOG_MESSAGE, ['booking' => $booking]);
                $this->messageBus->dispatch(ChannelManagerBookingRequest::fromRejectedBooking($booking));
                break;

            case BookingStatusConstraint::BOOKING_STATUS_PENDING_PARTNER_CONFIRMATION:
                $this->logger->info(
                    BookingStatusEvent::PENDING_PARTNER_CONFIRMATION_BOOKING_LOG_MESSAGE,
                    ['booking' => $booking]
                );
                break;
        }
    }
}
