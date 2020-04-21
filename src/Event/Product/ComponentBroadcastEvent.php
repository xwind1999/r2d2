<?php

declare(strict_types=1);

namespace App\Event\Product;

use App\Contract\Request\BroadcastListener\ProductRequest;
use App\Event\Product\Contract\ProductRequestEventInterface;
use Symfony\Contracts\EventDispatcher\Event;

class ComponentBroadcastEvent extends Event implements ProductRequestEventInterface
{
    public const EVENT_NAME = 'broadcast.component';
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
        return self::EVENT_NAME;
    }
}
