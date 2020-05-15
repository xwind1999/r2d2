<?php

declare(strict_types=1);

namespace App\EventSubscriber\ProductBroadcast;

use App\Event\Product\ComponentBroadcastEvent;
use App\Exception\Repository\PartnerNotFoundException;
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
        } catch (PartnerNotFoundException $exception) {
            $this->logger->warning(
                'No existing Partner for this component',
                $event->getProductRequest()->getContext()
            );

            throw $exception;
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage(), $event->getProductRequest()->getContext());

            throw $exception;
        }
    }
}
