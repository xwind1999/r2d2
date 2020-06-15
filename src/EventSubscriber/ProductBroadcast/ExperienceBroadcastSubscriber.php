<?php

declare(strict_types=1);

namespace App\EventSubscriber\ProductBroadcast;

use App\Event\Product\ExperienceBroadcastEvent;
use App\Exception\ContextualException;
use App\Manager\ExperienceManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ExperienceBroadcastSubscriber implements EventSubscriberInterface
{
    private LoggerInterface $logger;
    private ExperienceManager $experienceManager;

    public function __construct(LoggerInterface $logger, ExperienceManager $experienceManager)
    {
        $this->logger = $logger;
        $this->experienceManager = $experienceManager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ExperienceBroadcastEvent::class => ['handleMessage'],
        ];
    }

    public function handleMessage(ExperienceBroadcastEvent $event): void
    {
        try {
            $this->experienceManager->replace($event->getProductRequest());
        } catch (ContextualException $exception) {
            $this->logger->warning($exception, $event->getContext());

            throw $exception;
        } catch (\Exception $exception) {
            $this->logger->warning($exception->getMessage(), $event->getProductRequest()->getContext());

            throw $exception;
        }
    }
}
