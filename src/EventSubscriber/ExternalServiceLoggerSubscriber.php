<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Event\Http\ExternalServiceRequestMadeEvent;
use App\Event\Http\MalformedResponseReceivedEvent;
use App\Event\Http\WellFormedResponseReceivedEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ExternalServiceLoggerSubscriber implements EventSubscriberInterface
{
    protected const SUBSCRIBED_EVENTS = [
        WellFormedResponseReceivedEvent::class => 'logWellFormedResponseReceived',
        MalformedResponseReceivedEvent::class => 'logMalformedResponseReceived',
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

    public function logMalformedResponseReceived(MalformedResponseReceivedEvent $event): void
    {
        $this->logger->error($event);
    }

    public function logRequestMade(ExternalServiceRequestMadeEvent $event): void
    {
        $this->logger->notice($event);
    }
}
