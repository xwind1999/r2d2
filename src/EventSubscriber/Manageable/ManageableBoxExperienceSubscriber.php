<?php

declare(strict_types=1);

namespace App\EventSubscriber\Manageable;

use App\Contract\Request\BroadcastListener\ProductRequest;
use App\Contract\Request\Manageable\ManageableProductRequest;
use App\Event\Manageable\ManageableBoxExperienceEvent;
use App\Repository\BoxExperienceRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class ManageableBoxExperienceSubscriber implements EventSubscriberInterface
{
    private LoggerInterface $logger;
    private BoxExperienceRepository $boxExperienceRepository;
    private MessageBusInterface $messageBus;

    public function __construct(LoggerInterface $logger, BoxExperienceRepository $boxExperienceRepository, MessageBusInterface $messageBus)
    {
        $this->logger = $logger;
        $this->boxExperienceRepository = $boxExperienceRepository;
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
        $boxExperienceList = $this->boxExperienceRepository->findBy(['boxGoldenId' => $event->boxGoldenId]);
        foreach ($boxExperienceList as $boxExperience) {
            $manageableProductRequest = new ManageableProductRequest();
            try {
                $manageableProductRequest->setProductRequest(
                    ProductRequest::fromBoxExperience($boxExperience)
                );
                $this->messageBus->dispatch($manageableProductRequest);
            } catch (\Exception $exception) {
                $this->logger->error($exception, $event->getContext());
            }
        }
    }
}
