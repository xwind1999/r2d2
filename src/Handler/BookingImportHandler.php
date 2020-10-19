<?php

declare(strict_types=1);

namespace App\Handler;

use App\Contract\Request\Booking\BookingImport\BookingImportRequest;
use App\Manager\BookingManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class BookingImportHandler implements MessageHandlerInterface
{
    private LoggerInterface $logger;
    private BookingManager $bookingManager;

    public function __construct(LoggerInterface $logger, BookingManager $bookingManager)
    {
        $this->logger = $logger;
        $this->bookingManager = $bookingManager;
    }

    public function __invoke(BookingImportRequest $bookingImportRequest): void
    {
        try {
            $this->bookingManager->import($bookingImportRequest);
        } catch (\Exception $exception) {
            $this->logger->error($exception, $bookingImportRequest->getContext());

            throw $exception;
        }
    }
}
