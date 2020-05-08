<?php

declare(strict_types=1);

namespace App\Tests\Event\ProductRelationship;

use App\Contract\Request\BroadcastListener\ProductRelationshipRequest;
use App\Event\ProductRelationship\ExperienceComponentRelationshipBroadcastEvent;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \App\Event\ProductRelationship\ExperienceComponentRelationshipBroadcastEvent
 */
class ExperienceComponentRelationshipBroadcastEventTest extends TestCase
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
