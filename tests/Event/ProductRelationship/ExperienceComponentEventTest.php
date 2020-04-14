<?php

declare(strict_types=1);

namespace App\Tests\Event\ProductRelationship;

use App\Contract\Request\BroadcastListener\RelationshipRequest;
use App\Event\ProductRelationship\ExperienceComponentEvent;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \App\Event\ProductRelationship\ExperienceComponentEvent
 */
class ExperienceComponentEventTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::getRelationshipRequest
     */
    public function testEvent(): void
    {
        $relationshipRequest = $this->createMock(RelationshipRequest::class);

        $experienceComponentEvent = new ExperienceComponentEvent($relationshipRequest);
        $this->assertInstanceOf(RelationshipRequest::class, $experienceComponentEvent->getRelationshipRequest());
    }
}
