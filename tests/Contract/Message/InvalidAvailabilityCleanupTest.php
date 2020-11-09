<?php

declare(strict_types=1);

namespace App\Tests\Contract\Message;

use App\Contract\Message\InvalidAvailabilityCleanup;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \App\Contract\Message\InvalidAvailabilityCleanup
 */
class InvalidAvailabilityCleanupTest extends TestCase
{
    /**
     * @covers ::getContext
     */
    public function testGetContext(): void
    {
        $message = new InvalidAvailabilityCleanup();
        $this->assertEquals([], $message->getContext());
    }

    /**
     * @covers ::getEventName
     */
    public function testGetEventName(): void
    {
        $message = new InvalidAvailabilityCleanup();
        $this->assertEquals('Invalid availability cleanup', $message->getEventName());
    }
}
