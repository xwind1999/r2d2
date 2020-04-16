<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Event\ProductRelationship\ExperienceComponentRelationshipBroadcastEvent;
use App\Exception\Repository\ExperienceNotFoundException;
use App\Exception\Repository\RoomNotFoundException;
use App\Handler\ProductRelationshipBroadcastHandler;
use App\Manager\ExperienceComponentManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ExperienceComponentSubscriber implements EventSubscriberInterface
{
    private LoggerInterface $logger;
    private ExperienceComponentManager $experienceComponentManager;

    public function __construct(LoggerInterface $logger, ExperienceComponentManager $experienceComponentManager)
    {
        $this->logger = $logger;
        $this->experienceComponentManager = $experienceComponentManager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ProductRelationshipBroadcastHandler::EXPERIENCE_COMPONENT_EVENT => ['handleMessage'],
        ];
    }

    public function handleMessage(ExperienceComponentRelationshipBroadcastEvent $event): void
    {
        try {
            $this->experienceComponentManager->replace($event->getRelationshipRequest());
        } catch (ExperienceNotFoundException $exception) {
            $this->logger->warning(
                'No existing Experience for this relationship',
                [
                    'parent_product' => $event->getRelationshipRequest()->parentProduct,
                ]
            );
        } catch (RoomNotFoundException $exception) {
            $this->logger->warning(
                'No existing Room for this relationship',
                [
                    'child_product' => $event->getRelationshipRequest()->childProduct,
                ]
            );
        }
    }
}
