<?php

declare(strict_types=1);

namespace App\Tests\Event\QuickData;

use App\Event\QuickData\BoxCacheMissEvent;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \App\Event\Quickdata\BoxCacheMissEvent
 */
class BoxCacheMissEventTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::getEventName
     */
    public function testGetEventName()
    {
        $event = new BoxCacheMissEvent('1234', '5678');
        $this->assertEquals('QuickData box cache miss', $event->getEventName());
    }

    /**
     * @covers ::__construct
     * @covers ::getContext
     */
    public function testGetContext()
    {
        $exception = new \Exception();
        $event = new BoxCacheMissEvent('1234', '5678');

        $this->assertEquals(
            [
                'boxGoldenId' => '1234',
                'date' => '5678',
            ],
            $event->getContext()
        );
    }
}
