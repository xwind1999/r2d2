<?php

declare(strict_types=1);

namespace App\Tests\Event\ProductRelationship;

use App\Contract\Request\BroadcastListener\ProductRelationshipRequest;
use App\Event\ProductRelationship\BoxExperienceRelationshipBroadcastEvent;
use App\Tests\ProphecyTestCase;

/**
 * @coversDefaultClass \App\Event\ProductRelationship\BoxExperienceRelationshipBroadcastEvent
 */
class BoxExperienceEventTest extends ProphecyTestCase
{
    /**
     * @covers ::__construct
     * @covers ::getProductRelationshipRequest
     */
    public function testEvent(): void
    {
        $relationshipRequest = $this->createMock(ProductRelationshipRequest::class);

        $boxExperienceEvent = new BoxExperienceRelationshipBroadcastEvent($relationshipRequest);
        $this->assertInstanceOf(ProductRelationshipRequest::class, $boxExperienceEvent->getProductRelationshipRequest());
    }
}
