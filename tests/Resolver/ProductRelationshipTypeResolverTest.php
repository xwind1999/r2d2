<?php

declare(strict_types=1);

namespace App\Tests\QuickData;

use App\Contract\Request\BroadcastListener\RelationshipRequest;
use App\Event\ProductRelationship\BoxExperienceRelationshipBroadcastEvent;
use App\Event\ProductRelationship\ExperienceComponentRelationshipBroadcastEvent;
use App\Resolver\Exception\NonExistentTypeResolverExcepetion;
use App\Resolver\ProductRelationshipTypeResolver;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \App\Resolver\ProductRelationshipTypeResolver
 */
class ProductRelationshipTypeResolverTest extends TestCase
{
    /**
     * @covers ::resolve
     */
    public function testResolveExperienceComponentSuccessfully()
    {
        $relationshipRequest = $this->createMock(RelationshipRequest::class);
        $relationshipRequest->relationshipType = 'Experience-Component';

        $relationshipTypeResolver = new ProductRelationshipTypeResolver();
        $experienceComponentEvent = $relationshipTypeResolver->resolve($relationshipRequest);
        $this->assertInstanceOf(ExperienceComponentRelationshipBroadcastEvent::class, $experienceComponentEvent);
    }

    /**
     * @covers ::resolve
     */
    public function testResolveBoxExperienceSuccessfully()
    {
        $relationshipRequest = $this->createMock(RelationshipRequest::class);
        $relationshipRequest->relationshipType = 'Box-Experience';

        $relationshipTypeResolver = new ProductRelationshipTypeResolver();
        $boxExperienceEvent = $relationshipTypeResolver->resolve($relationshipRequest);
        $this->assertInstanceOf(BoxExperienceRelationshipBroadcastEvent::class, $boxExperienceEvent);
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
