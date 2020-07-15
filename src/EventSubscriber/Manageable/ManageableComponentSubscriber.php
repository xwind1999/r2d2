<?php

declare(strict_types=1);

namespace App\EventSubscriber\Manageable;

use App\Contract\Request\EAI\RoomRequest;
use App\Event\Manageable\ManageableComponentEvent;
use App\Manager\ComponentManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class ManageableComponentSubscriber implements EventSubscriberInterface
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
            ManageableComponentEvent::class => ['handleMessage'],
        ];
    }

    public function handleMessage(ManageableComponentEvent $event): void
    {
        try {
            $component = $this->manager->findAndSetManageableComponent($event->componentGoldenId);
            //TODO: only send if changed
            $this->messageBus->dispatch(RoomRequest::transformFromComponent($component));
        } catch (\Exception $exception) {
            $this->logger->error($exception, $event->getContext());

            throw $exception;
        }
    }
}
