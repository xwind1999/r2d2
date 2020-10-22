<?php

declare(strict_types=1);

namespace App\Tests\Event\ProductRelationship;

use App\Contract\Request\BroadcastListener\ProductRelationshipRequest;
use App\Event\ProductRelationship\ExperienceComponentRelationshipBroadcastEvent;
use App\Tests\ProphecyTestCase;

/**
 * @coversDefaultClass \App\Event\ProductRelationship\ExperienceComponentRelationshipBroadcastEvent
 */
class ExperienceComponentRelationshipBroadcastEventTest extends ProphecyTestCase
{
    /**
     * @covers ::__construct
     * @covers ::getProductRelationshipRequest
     */
    public function testEvent(): void
    {
        $relationshipRequest = $this->createMock(ProductRelationshipRequest::class);

        $experienceComponentEvent = new ExperienceComponentRelationshipBroadcastEvent($relationshipRequest);
        $this->assertInstanceOf(ProductRelationshipRequest::class, $experienceComponentEvent->getProductRelationshipRequest());
    }
}
