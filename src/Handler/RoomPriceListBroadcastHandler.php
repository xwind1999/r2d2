<?php

declare(strict_types=1);

namespace App\Handler;

use App\Contract\Request\BroadcastListener\RoomPriceRequestList;
use App\Manager\RoomPriceManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class RoomPriceListBroadcastHandler implements MessageHandlerInterface
{
    private LoggerInterface $logger;
    private RoomPriceManager $roomPriceManager;

    public function __construct(LoggerInterface $logger, RoomPriceManager $roomPriceManager)
    {
        $this->logger = $logger;
        $this->roomPriceManager = $roomPriceManager;
    }

    public function __invoke(RoomPriceRequestList $roomPriceRequestList): void
    {
        try {
            $this->roomPriceManager->dispatchRoomPricesRequest($roomPriceRequestList);
        } catch (\Exception $exception) {
            $this->logger->warning($exception, $roomPriceRequestList->getContext());

            throw $exception;
        }
    }
}
