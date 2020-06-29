<?php

declare(strict_types=1);

namespace App\Handler;

use App\Contract\Request\Manageable\ManageableProductRequest;
use App\Manager\ComponentManager;
use App\Resolver\ManageableProductResolver;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class ManageableProductHandler implements MessageHandlerInterface
{
    private LoggerInterface $logger;
    private ManageableProductResolver $manageableProductResolver;
    private ComponentManager $componentManager;

    public function __construct(
        LoggerInterface $logger,
        ManageableProductResolver $manageableProductResolver,
        ComponentManager $componentManager
    ) {
        $this->logger = $logger;
        $this->manageableProductResolver = $manageableProductResolver;
        $this->componentManager = $componentManager;
    }

    public function __invoke(ManageableProductRequest $manageableProductRequest): void
    {
        try {
            $product = $this->manageableProductResolver->resolve($manageableProductRequest);
            $this->componentManager->findAndSetManageableComponent($product);
        } catch (\Exception $exception) {
            $this->logger->warning($exception, $manageableProductRequest->getContext());
        }
    }
}
