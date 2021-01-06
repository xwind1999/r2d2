<?php

declare(strict_types=1);

namespace App\EventSubscriber\Booking;

use App\Constraint\BookingStatusConstraint;
use App\Event\Booking\BookingStatusEvent;
use App\Manager\RoomAvailabilityManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class BookingAvailabilitySubscriber implements EventSubscriberInterface
{
    private RoomAvailabilityManager $roomAvailabilityManager;
    private LoggerInterface $logger;

    public function __construct(
        RoomAvailabilityManager $roomAvailabilityManager,
        LoggerInterface $logger
    ) {
        $this->roomAvailabilityManager = $roomAvailabilityManager;
        $this->logger = $logger;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            BookingStatusEvent::class => ['handleBookingAvailability'],
        ];
    }

    public function handleBookingAvailability(BookingStatusEvent $event): void
    {
        try {
            if (BookingStatusConstraint::BOOKING_STATUS_COMPLETE === $event->getBooking()->status) {
                $this->roomAvailabilityManager->updateStockBookingConfirmation($event->getBooking());
            } elseif (
                BookingStatusConstraint::BOOKING_STATUS_CANCELLED === $event->getBooking()->status
                && BookingStatusConstraint::BOOKING_STATUS_COMPLETE === $event->getPreviousBookingStatus()
            ) {
                $this->roomAvailabilityManager->updateStockBookingCancellation($event->getBooking());
            }
        } catch (\Exception $exception) {
            $this->logger->warning($exception->getMessage(), ['booking' => $event->getBooking()]);
        }
    }
}
