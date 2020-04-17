<?php

declare(strict_types=1);

namespace App\Tests\Event\ProductRelationship;

use App\Contract\Request\BroadcastListener\RelationshipRequest;
use App\Event\ProductRelationship\BoxExperienceRelationshipBroadcastEvent;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \App\Event\ProductRelationship\BoxExperienceRelationshipBroadcastEvent
 */
class BoxExperienceEventTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::getRelationshipRequest
     * @covers ::getEventName
     */
    public function testEvent(): void
    {
        $relationshipRequest = $this->createMock(RelationshipRequest::class);

        $boxExperienceEvent = new BoxExperienceRelationshipBroadcastEvent($relationshipRequest);
        $this->assertInstanceOf(RelationshipRequest::class, $boxExperienceEvent->getRelationshipRequest());
        $this->assertEquals(BoxExperienceRelationshipBroadcastEvent::EVENT_NAME, $boxExperienceEvent->getEventName());
    }
}
