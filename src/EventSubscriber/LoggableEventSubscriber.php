<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Event\Http\BadResponseReceivedEvent;
use App\Event\Http\ExternalServiceRequestMadeEvent;
use App\Event\Http\WellFormedResponseReceivedEvent;
use App\Helper\LoggableEventInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class LoggableEventSubscriber implements EventSubscriberInterface
{
    protected LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LoggableEventInterface::class => ['logEvent', 100],
            BadResponseReceivedEvent::class => ['logEvent', 100],
            ExternalServiceRequestMadeEvent::class => ['logEvent', 100],
            WellFormedResponseReceivedEvent::class => ['logEvent', 100],
        ];
    }

    public function logEvent(LoggableEventInterface $event): void
    {
        $this->logger->log($event->getLevel(), $event);
    }
}
