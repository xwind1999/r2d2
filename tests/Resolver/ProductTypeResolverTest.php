<?php

declare(strict_types=1);

namespace App\Tests\QuickData;

use App\Contract\Request\BroadcastListener\ProductRequest;
use App\Event\Product\ComponentBroadcastEvent;
use App\Resolver\Exception\NonExistentTypeResolverExcepetion;
use App\Resolver\ProductTypeResolver;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @coversDefaultClass \App\Resolver\ProductTypeResolver
 */
class ProductTypeResolverTest extends TestCase
{
    /**
     * @var ObjectProphecy|ProductRequest
     */
    private $productRequest;

    protected function setUp(): void
    {
        $this->productRequest = $this->prophesize(ProductRequest::class);
    }

    /**
     * @covers ::resolve
     */
    public function testResolveUsingBoxSuccessfully()
    {
        $this->productRequest->type = 'mev';

        $relationshipTypeResolver = new ProductTypeResolver();
        $event = $relationshipTypeResolver->resolve($this->productRequest->reveal());
        $this->assertInstanceOf(Event::class, $event);
    }

    /**
     * @covers ::resolve
     */
    public function testResolveUsingExperienceSuccessfully()
    {
        $this->productRequest->type = 'experience';

        $relationshipTypeResolver = new ProductTypeResolver();
        $event = $relationshipTypeResolver->resolve($this->productRequest->reveal());
        $this->assertInstanceOf(Event::class, $event);
    }

    /**
     * @covers ::resolve
     */
    public function testResolveUsingBoxThrowsNonExistentTypeResolverExcepetion()
    {
        $this->productRequest->type = 'Invalid-type';

        $this->expectException(NonExistentTypeResolverExcepetion::class);

        $relationshipTypeResolver = new ProductTypeResolver();
        $relationshipTypeResolver->resolve($this->productRequest->reveal());
    }

    /**
     * @covers ::resolve
     */
    public function testResolveUsingComponentSuccessfully()
    {
        $productRequest = $this->createMock(ProductRequest::class);
        $productRequest->type = 'component';

        $relationshipTypeResolver = new ProductTypeResolver();
        $event = $relationshipTypeResolver->resolve($productRequest);
        $this->assertInstanceOf(ComponentBroadcastEvent::class, $event);
    }
}
