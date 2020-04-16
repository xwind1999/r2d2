<?php

declare(strict_types=1);

namespace App\Tests\Event\ProductRelationship;

use App\Contract\Request\BroadcastListener\RelationshipRequest;
use App\Event\ProductRelationship\ExperienceComponentRelationshipBroadcastEvent;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \App\Event\ProductRelationship\ExperienceComponentRelationshipBroadcastEvent
 */
class ExperienceComponentRelationshipBroadcastEventTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::getRelationshipRequest
     */
    public function testEvent(): void
    {
        $relationshipRequest = $this->createMock(RelationshipRequest::class);

        $experienceComponentEvent = new ExperienceComponentRelationshipBroadcastEvent($relationshipRequest);
        $this->assertInstanceOf(RelationshipRequest::class, $experienceComponentEvent->getRelationshipRequest());
    }
}
