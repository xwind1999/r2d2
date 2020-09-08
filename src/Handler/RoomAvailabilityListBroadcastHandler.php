<?php

declare(strict_types=1);

namespace App\Handler;

use App\Contract\Request\BroadcastListener\RoomAvailabilityRequestList;
use App\Manager\RoomAvailabilityManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class RoomAvailabilityListBroadcastHandler implements MessageHandlerInterface
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
    public function __invoke(RoomAvailabilityRequestList $roomAvailabilityRequestList): void
    {
        try {
            $this->roomAvailabilityManager->dispatchRoomAvailabilitiesRequest($roomAvailabilityRequestList);
        } catch (\Exception $exception) {
            $this->logger->warning($exception, $roomAvailabilityRequestList->getContext());

            throw $exception;
        }
    }
}
