<?php

declare(strict_types=1);

namespace App\EventSubscriber\Manageable;

use App\Contract\Request\EAI\RoomRequest;
use App\Event\Manageable\ManageableExperienceComponentEvent;
use App\Manager\ComponentManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class ManageableExperienceComponentSubscriber implements EventSubscriberInterface
{
    private LoggerInterface $logger;
    private ComponentManager $manager;
    private MessageBusInterface $messageBus;

    public function __construct(LoggerInterface $logger, ComponentManager $manager, MessageBusInterface $messageBus)
    {
        $this->logger = $logger;
        $this->manager = $manager;
        $this->messageBus = $messageBus;
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
            $component = $this->manager->findAndSetManageableComponent($event->componentGoldenId);
            $this->messageBus->dispatch(RoomRequest::transformFromComponent($component));
        } catch (\Exception $exception) {
            $this->logger->error($exception, $event->getContext());
        }
    }
}
