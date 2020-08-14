<?php

declare(strict_types=1);

namespace App\Handler;

use App\Contract\Request\EAI\RoomRequest;
use App\EAI\EAI;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class RoomRequestHandler implements MessageHandlerInterface
{
    private const SUCCESS_MESSAGE = 'Room pushed to EAI';

    private EAI $eaiProvider;
    private LoggerInterface $logger;

    public function __construct(EAI $eaiProvider, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->eaiProvider = $eaiProvider;
    }

    public function __invoke(RoomRequest $roomRequest): void
    {
        try {
            $this->eaiProvider->pushRoom($roomRequest);
            $this->logger->info(self::SUCCESS_MESSAGE, $roomRequest->getContext());
        } catch (\Exception $exception) {
            $this->logger->error($exception, $roomRequest->getContext());

            throw $exception;
        }
    }
}
