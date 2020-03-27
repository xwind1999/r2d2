<?php

declare(strict_types=1);

namespace App\Handler;

use App\Contract\Request\BroadcastListener\PartnerRequest;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class PartnerBroadcastHandler implements MessageHandlerInterface
{
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function __invoke(PartnerRequest $message): void
    {
        // TODO implement logic
        $this->logger->info('Consuming Partner message');
    }
}
