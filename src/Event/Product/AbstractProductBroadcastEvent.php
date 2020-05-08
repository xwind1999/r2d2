<?php

declare(strict_types=1);

namespace App\Event\Product;

use App\Contract\Request\BroadcastListener\ProductRequest;
use App\Event\Product\Contract\ProductRequestEventInterface;
use Clogger\ContextualInterface;
use Symfony\Contracts\EventDispatcher\Event;

abstract class AbstractProductBroadcastEvent extends Event implements ProductRequestEventInterface, ContextualInterface
{
    private ProductRequest $productRequest;

    public function __construct(ProductRequest $productRequest)
    {
        $this->productRequest = $productRequest;
    }

    public function getProductRequest(): ProductRequest
    {
        return $this->productRequest;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getContext(): array
    {
        return $this->productRequest->getContext();
    }
}
