<?php

declare(strict_types=1);

namespace App\EventSubscriber\ProductRelationshipBroadcast;

use App\Event\ProductRelationship\BoxExperienceRelationshipBroadcastEvent;
use App\Exception\ContextualException;
use App\Exception\Repository\BoxNotFoundException;
use App\Exception\Repository\ExperienceNotFoundException;
use App\Manager\BoxExperienceManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class BoxExperienceRelationshipSubscriber implements EventSubscriberInterface
{
    private LoggerInterface $logger;
    private BoxExperienceManager $boxExperienceManager;

    public function __construct(LoggerInterface $logger, BoxExperienceManager $boxExperienceManager)
    {
        $this->logger = $logger;
        $this->boxExperienceManager = $boxExperienceManager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            BoxExperienceRelationshipBroadcastEvent::class => ['handleMessage'],
        ];
    }

    public function handleMessage(BoxExperienceRelationshipBroadcastEvent $event): void
    {
        try {
            $this->boxExperienceManager->replace($event->getProductRelationshipRequest());
        } catch (BoxNotFoundException $exception) {
            $this->logger->warning(
                'No existing Box for this relationship',
                $event->getProductRelationshipRequest()->getContext()
            );

            throw $exception;
        } catch (ExperienceNotFoundException $exception) {
            $this->logger->warning(
                'No existing Experience for this relationship',
                $event->getProductRelationshipRequest()->getContext()
            );

            throw $exception;
        } catch (ContextualException $exception) {
            $this->logger->warning($exception, $event->getProductRelationshipRequest()->getContext());

            throw $exception;
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage(), $event->getProductRelationshipRequest()->getContext());

            throw $exception;
        }
    }
}
