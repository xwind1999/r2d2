<?php

declare(strict_types=1);

namespace App\EventSubscriber\Manageable;

use App\Event\Manageable\ManageableExperienceComponentEvent;
use App\Manager\ComponentManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ManageableExperienceComponentSubscriber implements EventSubscriberInterface
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
            ManageableExperienceComponentEvent::class => ['handleMessage'],
        ];
    }

    public function handleMessage(ManageableExperienceComponentEvent $event): void
    {
        try {
            $this->manager->calculateManageableFlag($event->componentGoldenId);
        } catch (\Exception $exception) {
            $this->logger->error($exception, $event->getContext());
        }
    }
}
