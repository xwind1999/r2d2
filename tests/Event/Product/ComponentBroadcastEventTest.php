<?php

declare(strict_types=1);

namespace App\Tests\Event\Product;

use App\Contract\Request\BroadcastListener\ProductRequest;
use App\Event\Product\ComponentBroadcastEvent;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \App\Event\Product\ComponentBroadcastEvent
 */
class ComponentBroadcastEventTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::getProductRequest
     */
    public function testEvent(): void
    {
        $productRequest = $this->prophesize(ProductRequest::class);

        $event = new ComponentBroadcastEvent($productRequest->reveal());
        $this->assertInstanceOf(ProductRequest::class, $event->getProductRequest());
    }
}
