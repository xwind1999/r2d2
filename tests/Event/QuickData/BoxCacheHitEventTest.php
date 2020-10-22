<?php

declare(strict_types=1);

namespace App\Tests\Event\QuickData;

use App\Event\QuickData\BoxCacheHitEvent;
use App\Tests\ProphecyTestCase;

/**
 * @coversDefaultClass \App\Event\Quickdata\BoxCacheHitEvent
 */
class BoxCacheHitEventTest extends ProphecyTestCase
{
    /**
     * @covers ::__construct
     * @covers ::getEventName
     */
    public function testGetEventName()
    {
        $event = new BoxCacheHitEvent('1234', '5678');
        $this->assertEquals('QuickData box cache hit', $event->getEventName());
    }

    /**
     * @covers ::__construct
     * @covers ::getContext
     */
    public function testGetContext()
    {
        $exception = new \Exception();
        $event = new BoxCacheHitEvent('1234', '5678');

        $this->assertEquals(
            [
                'boxGoldenId' => '1234',
                'date' => '5678',
            ],
            $event->getContext()
        );
    }
}
