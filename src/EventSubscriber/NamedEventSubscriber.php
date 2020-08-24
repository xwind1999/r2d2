<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Event\NamedEventInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\EventDispatcher\Event;

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
            Event::class => ['handleMessage'],
        ];
    }

    public function handleMessage(Event $event): void
    {
        if ($event instanceof NamedEventInterface) {
            $this->logger->info($event->getEventName());
        }
    }
}
