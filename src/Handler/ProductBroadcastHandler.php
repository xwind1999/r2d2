<?php

declare(strict_types=1);

namespace App\Handler;

use App\Contract\Request\BroadcastListener\ProductRequest;
use App\Resolver\Exception\NonExistentTypeResolverExcepetion;
use App\Resolver\ProductTypeResolver;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class ProductBroadcastHandler implements MessageHandlerInterface
{
    private LoggerInterface $logger;
    private ProductTypeResolver $productTypeResolver;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        LoggerInterface $logger,
        ProductTypeResolver $productTypeResolver,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->logger = $logger;
        $this->productTypeResolver = $productTypeResolver;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function __invoke(ProductRequest $productRequest): void
    {
        try {
            $event = $this->productTypeResolver->resolve($productRequest);
            $this->eventDispatcher->dispatch($event);
        } catch (NonExistentTypeResolverExcepetion $exception) {
            $this->logger->warning($exception->getMessage(), $productRequest->getContext());
        }
    }
}
