<?php

declare(strict_types=1);

namespace App\Tests\Event\Product;

use App\Contract\Request\BroadcastListener\ProductRequest;
use App\Event\Product\ExperienceBroadcastEvent;
use App\Tests\ProphecyTestCase;

/**
 * @coversDefaultClass \App\Event\Product\ExperienceBroadcastEvent
 */
class ExperienceBroadcastEventTest extends ProphecyTestCase
{
    /**
     * @covers ::__construct
     * @covers ::getProductRequest
     */
    public function testEvent(): void
    {
        $productRequest = $this->prophesize(ProductRequest::class);

        $event = new ExperienceBroadcastEvent($productRequest->reveal());
        $this->assertInstanceOf(ProductRequest::class, $event->getProductRequest());
    }
}
