<?php

declare(strict_types=1);

namespace App\Command;

use App\Constraint\BookingStatusConstraint;
use App\Contract\Request\EAI\ChannelManagerBookingRequest;
use App\Helper\CSVParser;
use App\Repository\BookingRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Messenger\MessageBusInterface;

class PushBookingsToCmhCommand extends BulkProcessAbstractCommand
{
    protected static $defaultName = 'r2d2:eai:push-bookings';

    private BookingRepository $bookingRepository;

    public function __construct(
        CSVParser $csvParser,
        LoggerInterface $logger,
        MessageBusInterface $messageBus,
        BookingRepository $bookingRepository
    ) {
        $this->bookingRepository = $bookingRepository;
        parent::__construct($csvParser, $logger, $messageBus);
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Command to send bookings to CMH')
            ->addArgument('file', InputArgument::REQUIRED, 'CSV file path')
            ->addArgument('batchSize', InputArgument::REQUIRED, 'BATCH SIZE')
        ;
    }

    protected function process(array $goldenIdList): void
    {
        $bookings = $this->bookingRepository->findListByGoldenId($goldenIdList);
        $this->dataTotal += count($bookings);
        foreach ($bookings as $key => $booking) {
            if (BookingStatusConstraint::BOOKING_STATUS_COMPLETE === $booking->status) {
                $this->messageBus->dispatch(ChannelManagerBookingRequest::fromCompletedBooking($booking));
            } elseif (BookingStatusConstraint::BOOKING_STATUS_CANCELLED === $booking->status) {
                $this->messageBus->dispatch(ChannelManagerBookingRequest::fromCancelledBooking($booking));
            }
        }
    }
}
