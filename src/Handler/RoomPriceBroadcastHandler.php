<?php

declare(strict_types=1);

namespace App\Handler;

use App\Contract\Request\BroadcastListener\RoomPriceRequest;
use App\Manager\RoomPriceManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class RoomPriceBroadcastHandler implements MessageHandlerInterface
{
    private LoggerInterface $logger;
    private RoomPriceManager $roomPriceManager;

    public function __construct(LoggerInterface $logger, RoomPriceManager $roomPriceManager)
    {
        $this->logger = $logger;
        $this->roomPriceManager = $roomPriceManager;
    }

    public function __invoke(RoomPriceRequest $roomPriceRequest): void
    {
        try {
            $this->roomPriceManager->replace($roomPriceRequest);
        } catch (\Exception $exception) {
            $this->logger->warning($exception, $roomPriceRequest->getContext());

            throw $exception;
        }
    }
}
