<?php

declare(strict_types=1);

namespace App\Tests\Event\Cleanup;

use App\Event\Cleanup\CleanupRoomAvailabilityEvent;
use PHPUnit\Framework\TestCase;

class CleanupRoomAvailabilityEventTest extends TestCase
{
    public function testGetEventName(): void
    {
        $this->assertEquals('Cleanup Room Availability', (new CleanupRoomAvailabilityEvent())->getEventName());
    }
}
