<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Event\Product\ComponentBroadcastEvent;
use App\Manager\ComponentManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ComponentBroadcastSubscriber implements EventSubscriberInterface
{
    private LoggerInterface $logger;
    private ComponentManager $manager;

    public function __construct(LoggerInterface $logger, ComponentManager $manager)
    {
        $this->logger = $logger;
        $this->manager = $manager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ComponentBroadcastEvent::class => ['handleMessage'],
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
