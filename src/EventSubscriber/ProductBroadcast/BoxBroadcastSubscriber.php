<?php

declare(strict_types=1);

namespace App\EventSubscriber\ProductBroadcast;

use App\Event\Product\BoxBroadcastEvent;
use App\Manager\BoxManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class BoxBroadcastSubscriber implements EventSubscriberInterface
{
    private LoggerInterface $logger;
    private BoxManager $boxManager;

    public function __construct(LoggerInterface $logger, BoxManager $boxManager)
    {
        $this->logger = $logger;
        $this->boxManager = $boxManager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            BoxBroadcastEvent::class => ['handleMessage'],
        ];
    }

    public function handleMessage(BoxBroadcastEvent $event): void
    {
        try {
            $this->boxManager->replace($event->getProductRequest());
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage(), $event->getProductRequest()->getContext());

            throw $exception;
        }
    }
}
