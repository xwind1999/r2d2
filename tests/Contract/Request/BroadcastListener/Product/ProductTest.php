<?php

declare(strict_types=1);

namespace App\Tests\Contract\Request\BroadcastListener\Product;

use App\Contract\Request\BroadcastListener\Product\Product;
use App\Tests\ProphecyTestCase;
use Ramsey\Uuid\Uuid;

/**
 * @coversDefaultClass \App\Contract\Request\BroadcastListener\Product\Product
 * @group broadcast-product
 */
class ProductTest extends ProphecyTestCase
{
    /**
     * @covers ::getContext
     */
    public function testGetContext()
    {
        $product = new Product();
        $product->id = Uuid::uuid4()->toString();

        $context = $product->getContext();

        $this->assertIsArray($context);
        $this->assertArrayHasKey('id', $context);
        $this->assertEquals($product->id, $context['id']);
    }

    /**
     * @covers ::getContext
     */
    public function testGetContextIdAsInt()
    {
        $this->expectException(\TypeError::class);
        $product = new Product();
        $product->id = 12345;

        $product->getContext();
    }
}
