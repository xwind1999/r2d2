<?php

declare(strict_types=1);

namespace App\Tests\Event\Cleanup;

use App\Event\Cleanup\CleanupRoomAvailabilityEvent;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \App\Event\Cleanup\CleanupRoomAvailabilityEvent
 */
class CleanupRoomAvailabilityEventTest extends TestCase
{
    /**
     * @covers ::getEventName
     */
    public function testGetEventName(): void
    {
        $this->assertEquals('Cleanup Room Availability', (new CleanupRoomAvailabilityEvent())->getEventName());
    }

    /**
     * @covers ::getContext
     */
    public function testGetContext(): void
    {
        $this->assertEquals([], (new CleanupRoomAvailabilityEvent())->getContext());
    }
}
