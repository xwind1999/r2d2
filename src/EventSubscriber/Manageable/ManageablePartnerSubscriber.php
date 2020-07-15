<?php

declare(strict_types=1);

namespace App\EventSubscriber\Manageable;

use App\Contract\Request\BroadcastListener\ProductRequest;
use App\Contract\Request\Manageable\ManageableProductRequest;
use App\Event\Manageable\ManageablePartnerEvent;
use App\Repository\ComponentRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class ManageablePartnerSubscriber implements EventSubscriberInterface
{
    private LoggerInterface $logger;
    private ComponentRepository $componentRepository;
    private MessageBusInterface $messageBus;

    public function __construct(
        LoggerInterface $logger,
        ComponentRepository $componentRepository,
        MessageBusInterface $messageBus
    ) {
        $this->logger = $logger;
        $this->componentRepository = $componentRepository;
        $this->messageBus = $messageBus;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ManageablePartnerEvent::class => ['handleMessage'],
        ];
    }

    public function handleMessage(ManageablePartnerEvent $event): void
    {
        $componentList = $this->componentRepository->findListByPartner($event->partnerGoldenId);

        foreach ($componentList as $component) {
            $manageableProductRequest = new ManageableProductRequest();

            try {
                $manageableProductRequest->setProductRequest(ProductRequest::fromComponent($component));

                $this->messageBus->dispatch($manageableProductRequest);
            } catch (\Exception $exception) {
                $this->logger->error($exception, $event->getContext());
            }
        }
    }
}
