<?php

declare(strict_types=1);

namespace App\Tests\QuickData;

use App\Contract\Request\BroadcastListener\ProductRequest;
use App\Resolver\Exception\NonExistentTypeResolverExcepetion;
use App\Resolver\ProductTypeResolver;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @coversDefaultClass \App\Resolver\ProductTypeResolver
 */
class ProductTypeResolverTest extends TestCase
{
    /**
     * @covers ::resolve
     */
    public function testResolveSuccessfully()
    {
        $productRequest = $this->createMock(ProductRequest::class);
        $productRequest->type = 'mev';

        $relationshipTypeResolver = new ProductTypeResolver();
        $event = $relationshipTypeResolver->resolve($productRequest);
        $this->assertInstanceOf(Event::class, $event);
    }

    /**
     * @covers ::resolve
     */
    public function testResolveThrowsNonExistentTypeResolverExcepetion()
    {
        $productRequest = $this->createMock(ProductRequest::class);
        $productRequest->type = 'Invalid-type';

        $this->expectException(NonExistentTypeResolverExcepetion::class);

        $relationshipTypeResolver = new ProductTypeResolver();
        $relationshipTypeResolver->resolve($productRequest);
    }
}
