<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Event\Product\ComponentBroadcastEvent;
use App\Manager\RoomManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ComponentBroadcastSubscriber implements EventSubscriberInterface
{
    private LoggerInterface $logger;
    private RoomManager $manager;

    public function __construct(LoggerInterface $logger, RoomManager $manager)
    {
        $this->logger = $logger;
        $this->manager = $manager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ComponentBroadcastEvent::EVENT_NAME => ['handleMessage'],
        ];
    }

    public function handleMessage(ComponentBroadcastEvent $event): void
    {
        try {
            $this->manager->replace($event->getProductRequest());
        } catch (\Exception $exception) {
            $this->logger->warning($exception->getMessage(), $event->getProductRequest()->getContext());
        }
    }
}
