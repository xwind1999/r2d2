<?php

declare(strict_types=1);

namespace App\Handler;

use App\Contract\Request\BroadcastListener\ProductRelationshipRequest;
use App\Resolver\Exception\NonExistentTypeResolverExcepetion;
use App\Resolver\ProductRelationshipTypeResolver;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class ProductRelationshipBroadcastHandler implements MessageHandlerInterface
{
    private LoggerInterface $logger;
    private ProductRelationshipTypeResolver $productRelationshipTypeResolver;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        LoggerInterface $logger,
        ProductRelationshipTypeResolver $productRelationshipTypeResolver,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->logger = $logger;
        $this->productRelationshipTypeResolver = $productRelationshipTypeResolver;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function __invoke(ProductRelationshipRequest $relationshipRequest): void
    {
        try {
            $productRelationshipEvent = $this->productRelationshipTypeResolver->resolve($relationshipRequest);
            $this->eventDispatcher->dispatch($productRelationshipEvent);
        } catch (NonExistentTypeResolverExcepetion $exception) {
            $this->logger->warning($exception->getMessage(), $relationshipRequest->getContext());
        }
    }
}
