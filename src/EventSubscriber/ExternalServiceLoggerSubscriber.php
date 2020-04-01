<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Event\Http\BadResponseReceivedEvent;
use App\Event\Http\ExternalServiceRequestMadeEvent;
use App\Event\Http\WellFormedResponseReceivedEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ExternalServiceLoggerSubscriber implements EventSubscriberInterface
{
    protected const SUBSCRIBED_EVENTS = [
        WellFormedResponseReceivedEvent::class => 'logWellFormedResponseReceived',
        BadResponseReceivedEvent::class => 'logBadResponseReceived',
        ExternalServiceRequestMadeEvent::class => 'logRequestMade',
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

    public function logWellFormedResponseReceived(WellFormedResponseReceivedEvent $event): void
    {
        $this->logger->notice($event);
    }

    public function logBadResponseReceived(BadResponseReceivedEvent $event): void
    {
        $this->logger->error($event);
    }

    public function logRequestMade(ExternalServiceRequestMadeEvent $event): void
    {
        $this->logger->notice($event);
    }
}
