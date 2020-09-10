<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Event\NamedEventInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class NamedEventSubscriber implements EventSubscriberInterface
{
    private LoggerInterface  $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            NamedEventInterface::class => ['handleMessage'],
        ];
    }

    public function handleMessage(NamedEventInterface $event): void
    {
        $this->logger->info($event->getEventName(), $event->getContext());
    }
}
