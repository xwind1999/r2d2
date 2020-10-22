<?php

declare(strict_types=1);

namespace App\Tests\Event\Product;

use App\Contract\Request\BroadcastListener\ProductRequest;
use App\Event\Product\BoxBroadcastEvent;
use App\Tests\ProphecyTestCase;

/**
 * @coversDefaultClass \App\Event\Product\BoxBroadcastEvent
 */
class BoxBroadcastEventTest extends ProphecyTestCase
{
    /**
     * @covers ::__construct
     * @covers ::getProductRequest
     */
    public function testEvent(): void
    {
        $productRequest = $this->prophesize(ProductRequest::class);

        $event = new BoxBroadcastEvent($productRequest->reveal());
        $this->assertInstanceOf(ProductRequest::class, $event->getProductRequest());
    }
}
