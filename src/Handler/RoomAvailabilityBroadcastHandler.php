<?php

declare(strict_types=1);

namespace App\Handler;

use App\Contract\Request\BroadcastListener\RoomAvailabilityRequest;
use App\Manager\RoomAvailabilityManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class RoomAvailabilityBroadcastHandler implements MessageHandlerInterface
{
    private RoomAvailabilityManager $roomAvailabilityManager;
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger, RoomAvailabilityManager $roomAvailabilityManager)
    {
        $this->roomAvailabilityManager = $roomAvailabilityManager;
        $this->logger = $logger;
    }

    /**
     * @throws \Exception
     */
    public function __invoke(RoomAvailabilityRequest $roomAvailabilityRequest): void
    {
        try {
            $this->roomAvailabilityManager->replace($roomAvailabilityRequest);
        } catch (\Exception $exception) {
            $this->logger->warning($exception->getMessage());
            throw $exception;
        }
    }
}
