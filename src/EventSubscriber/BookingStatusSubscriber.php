<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\DBAL\BookingStatus;
use App\Entity\Booking;
use App\Event\BookingStatusEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class BookingStatusSubscriber implements EventSubscriberInterface
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
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

        if (isset($booking->expiresAt) && $booking->expiresAt < new \DateTime('now')) {
            $this->prepareMessage(BookingStatusEvent::LOG_MESSAGE_BOOKING_STATUS_EXPIRED, $booking);
        } elseif (BookingStatus::BOOKING_STATUS_CREATED === $booking->status) {
            $this->prepareMessage(BookingStatusEvent::LOG_MESSAGE_BOOKING_STATUS_CREATED, $booking);
        } elseif (BookingStatus::BOOKING_STATUS_COMPLETE === $booking->status) {
            $this->prepareMessage(BookingStatusEvent::LOG_MESSAGE_BOOKING_STATUS_COMPLETED, $booking);
        } elseif (BookingStatus::BOOKING_STATUS_CANCELLED === $booking->status) {
            $this->prepareMessage(BookingStatusEvent::LOG_MESSAGE_BOOKING_STATUS_CANCELLED, $booking);
        }
    }

    private function prepareMessage(string $message, Booking $booking): void
    {
        $this->logger->info($message, ['booking' => $booking]);
    }
}
