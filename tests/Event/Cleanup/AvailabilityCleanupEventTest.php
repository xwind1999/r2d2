<?php

declare(strict_types=1);

namespace App\Tests\Event\Cleanup;

use App\Event\Cleanup\AvailabilityCleanupEvent;
use App\Tests\ProphecyTestCase;

/**
 * @coversDefaultClass \App\Event\Cleanup\AvailabilityCleanupEvent
 */
class AvailabilityCleanupEventTest extends ProphecyTestCase
{
    /**
     * @covers ::getEventName
     */
    public function testGetEventName(): void
    {
        $this->assertEquals('Cleanup availability', (new AvailabilityCleanupEvent())->getEventName());
    }

    /**
     * @covers ::getContext
     */
    public function testGetContext(): void
    {
        $this->assertEquals([], (new AvailabilityCleanupEvent())->getContext());
    }
}
