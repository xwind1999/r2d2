<?php

declare(strict_types=1);

namespace App\EventSubscriber\Manageable;

use App\Contract\Request\BroadcastListener\ProductRequest;
use App\Contract\Request\Manageable\ManageableProductRequest;
use App\Event\Manageable\ManageableExperienceEvent;
use App\Repository\ExperienceComponentRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class ManageableExperienceSubscriber implements EventSubscriberInterface
{
    private LoggerInterface $logger;
    private ExperienceComponentRepository $experienceComponentRepository;
    private MessageBusInterface $messageBus;

    public function __construct(
        LoggerInterface $logger,
        ExperienceComponentRepository $experienceComponentRepository,
        MessageBusInterface $messageBus
    ) {
        $this->logger = $logger;
        $this->experienceComponentRepository = $experienceComponentRepository;
        $this->messageBus = $messageBus;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ManageableExperienceEvent::class => ['handleMessage'],
        ];
    }

    public function handleMessage(ManageableExperienceEvent $event): void
    {
        $experienceComponentList = $this->experienceComponentRepository->findBy(
                ['experienceGoldenId' => $event->experienceGoldenId]
            );
        foreach ($experienceComponentList as $experienceComponent) {
            $manageableProductRequest = new ManageableProductRequest();
            try {
                $manageableProductRequest->setProductRequest(
                    ProductRequest::fromExperienceComponent($experienceComponent)
                );
                $this->messageBus->dispatch($manageableProductRequest);
            } catch (\Exception $exception) {
                $this->logger->error($exception, $event->getContext());
            }
        }
    }
}
