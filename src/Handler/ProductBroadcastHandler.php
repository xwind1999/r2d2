<?php

declare(strict_types=1);

namespace App\Handler;

use App\Contract\Request\BroadcastListener\ProductRequest;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class ProductBroadcastHandler implements MessageHandlerInterface
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function __invoke(ProductRequest $message): void
    {
        // TODO implement logic
        $this->logger->info('Consuming Product message');
    }
}
