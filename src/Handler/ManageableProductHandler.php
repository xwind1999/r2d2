<?php

declare(strict_types=1);

namespace App\Handler;

use App\Contract\Request\Manageable\ManageableProductRequest;
use App\Resolver\ManageableProductResolver;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ManageableProductHandler implements MessageHandlerInterface
{
    private LoggerInterface $logger;
    private EventDispatcherInterface $eventDispatcher;
    private ManageableProductResolver $manageableProductResolver;

    public function __construct(
        LoggerInterface $logger,
        EventDispatcherInterface $eventDispatcher,
        ManageableProductResolver $manageableProductResolver
    ) {
        $this->logger = $logger;
        $this->eventDispatcher = $eventDispatcher;
        $this->manageableProductResolver = $manageableProductResolver;
    }

    public function __invoke(ManageableProductRequest $manageableProductRequest): void
    {
        try {
            $manageableEvent = $this->manageableProductResolver->resolve($manageableProductRequest);
            $this->eventDispatcher->dispatch($manageableEvent);
        } catch (\Exception $exception) {
            $this->logger->warning($exception, $manageableProductRequest->getContext());

            throw $exception;
        }
    }
}
