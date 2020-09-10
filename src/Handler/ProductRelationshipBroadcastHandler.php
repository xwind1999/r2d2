<?php

declare(strict_types=1);

namespace App\Handler;

use App\Contract\Request\BroadcastListener\ProductRelationshipRequest;
use App\Exception\Resolver\UnprocessableProductRelationshipTypeException;
use App\Resolver\ProductRelationshipTypeResolver;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

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
        } catch (UnprocessableProductRelationshipTypeException $exception) {
            $this->logger->warning($exception, $relationshipRequest->getContext());
        }
    }
}
