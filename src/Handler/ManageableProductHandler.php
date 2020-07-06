<?php

declare(strict_types=1);

namespace App\Handler;

use App\Contract\Request\EAI\RoomRequest;
use App\Contract\Request\Manageable\ManageableProductRequest;
use App\Manager\ComponentManager;
use App\Resolver\ManageableProductResolver;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class ManageableProductHandler implements MessageHandlerInterface
{
    private LoggerInterface $logger;
    private ComponentManager $componentManager;
    private MessageBusInterface $messageBus;
    private ManageableProductResolver $manageableProductResolver;

    public function __construct(
        LoggerInterface $logger,
        ComponentManager $componentManager,
        MessageBusInterface $messageBus,
        ManageableProductResolver $manageableProductResolver
    ) {
        $this->logger = $logger;
        $this->componentManager = $componentManager;
        $this->messageBus = $messageBus;
        $this->manageableProductResolver = $manageableProductResolver;
    }

    public function __invoke(ManageableProductRequest $manageableProductRequest): void
    {
        try {
            $product = $this->manageableProductResolver->resolve($manageableProductRequest);
            $component = $this->componentManager->findAndSetManageableComponent($product);
            $this->messageBus->dispatch(RoomRequest::transformFromComponent($component));
        } catch (\Exception $exception) {
            $this->logger->warning($exception, $manageableProductRequest->getContext());
        }
    }
}
