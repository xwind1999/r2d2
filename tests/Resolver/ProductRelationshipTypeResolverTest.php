<?php

declare(strict_types=1);

namespace App\Tests\QuickData;

use App\Contract\Request\BroadcastListener\RelationshipRequest;
use App\Resolver\Exception\NonExistentTypeResolverExcepetion;
use App\Resolver\ProductRelationshipTypeResolver;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @coversDefaultClass \App\Resolver\ProductRelationshipTypeResolver
 */
class ProductRelationshipTypeResolverTest extends TestCase
{
    /**
     * @covers ::resolve
     */
    public function testResolveSuccessfully()
    {
        $relationshipRequest = $this->createMock(RelationshipRequest::class);
        $relationshipRequest->relationshipType = 'Experience-Component';

        $relationshipTypeResolver = new ProductRelationshipTypeResolver();
        $event = $relationshipTypeResolver->resolve($relationshipRequest);
        $this->assertInstanceOf(Event::class, $event);
    }

    /**
     * @covers ::resolve
     */
    public function testResolveThrowsNonExistentTypeResolverExcepetion()
    {
        $relationshipRequest = $this->createMock(RelationshipRequest::class);
        $relationshipRequest->relationshipType = 'Component';

        $this->expectException(NonExistentTypeResolverExcepetion::class);

        $relationshipTypeResolver = new ProductRelationshipTypeResolver();
        $relationshipTypeResolver->resolve($relationshipRequest);
    }
}
