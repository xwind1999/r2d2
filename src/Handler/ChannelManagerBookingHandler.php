<?php

declare(strict_types=1);

namespace App\Handler;

use App\Contract\Request\EAI\ChannelManagerBookingRequest;
use App\EAI\EAI;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class ChannelManagerBookingHandler implements MessageHandlerInterface
{
    private const SUCCESS_MESSAGE = '%s booking pushed to EAI';

    private LoggerInterface $logger;
    private EAI $eaiProvider;

    public function __construct(LoggerInterface $logger, EAI $eaiProvider)
    {
        $this->logger = $logger;
        $this->eaiProvider = $eaiProvider;
    }

    public function __invoke(ChannelManagerBookingRequest $bookingRequest): void
    {
        try {
            $this->eaiProvider->pushChannelManagerBooking($bookingRequest);
            $this->logger->info(
                sprintf(self::SUCCESS_MESSAGE, $bookingRequest->getStatus()), $bookingRequest->getContext()
            );
        } catch (\Exception $exception) {
            $this->logger->error($exception, $bookingRequest->getContext());
            throw $exception;
        }
    }
}
