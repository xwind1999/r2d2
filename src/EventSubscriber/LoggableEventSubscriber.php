<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Helper\LoggableEventInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class LoggableEventSubscriber implements EventSubscriberInterface
{
    protected const SUBSCRIBED_EVENTS = [
        LoggableEventInterface::class => 'logEvent',
    ];

    protected LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public static function getSubscribedEvents(): array
    {
        return self::SUBSCRIBED_EVENTS;
    }

    public function logEvent(LoggableEventInterface $event): void
    {
        $this->logger->log($event->getLevel(), $event);
    }
}
