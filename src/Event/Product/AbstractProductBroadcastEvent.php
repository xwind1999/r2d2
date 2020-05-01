<?php

declare(strict_types=1);

namespace App\Event\Product;

use App\Contract\Request\BroadcastListener\ProductRequest;
use App\Event\AbstractLoggableEvent;
use App\Event\Product\Contract\ProductRequestEventInterface;

abstract class AbstractProductBroadcastEvent extends AbstractLoggableEvent implements ProductRequestEventInterface
{
    public const EVENT_NAME = 'product.broadcast';

    protected const LOG_MESSAGE = 'Product Broadcast Event';

    private ProductRequest $productRequest;

    public function __construct(ProductRequest $productRequest)
    {
        $this->productRequest = $productRequest;
    }

    public function getProductRequest(): ProductRequest
    {
        return $this->productRequest;
    }

    public function getEventName(): string
    {
        return static::EVENT_NAME;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getContext(): array
    {
        return $this->productRequest->getContext();
    }
}
