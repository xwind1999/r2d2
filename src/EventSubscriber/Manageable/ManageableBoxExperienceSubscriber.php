<?php

declare(strict_types=1);

namespace App\EventSubscriber\Manageable;

use App\Contract\Request\BroadcastListener\ProductRequest;
use App\Contract\Request\Manageable\ManageableProductRequest;
use App\Event\Manageable\ManageableBoxExperienceEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class ManageableBoxExperienceSubscriber implements EventSubscriberInterface
{
    private LoggerInterface $logger;
    private MessageBusInterface $messageBus;

    public function __construct(LoggerInterface $logger, MessageBusInterface $messageBus)
    {
        $this->logger = $logger;
        $this->messageBus = $messageBus;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ManageableBoxExperienceEvent::class => ['handleMessage'],
        ];
    }

    public function handleMessage(ManageableBoxExperienceEvent $event): void
    {
        $manageableProductRequest = new ManageableProductRequest();
        try {
            $manageableProductRequest->setProductRequest(ProductRequest::fromBoxExperience($event->experienceGoldenId));
            $this->messageBus->dispatch($manageableProductRequest);
        } catch (\Exception $exception) {
            $this->logger->error($exception, $event->getContext());
        }
    }
}
