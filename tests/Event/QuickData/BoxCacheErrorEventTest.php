<?php

declare(strict_types=1);

namespace App\Tests\Event\QuickData;

use App\Event\QuickData\BoxCacheErrorEvent;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \App\Event\Quickdata\BoxCacheErrorEvent
 */
class BoxCacheErrorEventTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::getEventName
     */
    public function testGetEventName()
    {
        $event = new BoxCacheErrorEvent('1234', '5678', new \Exception());
        $this->assertEquals('QuickData box cache error', $event->getEventName());
    }

    /**
     * @covers ::__construct
     * @covers ::getContext
     */
    public function testGetContext()
    {
        $exception = new \Exception();
        $event = new BoxCacheErrorEvent('1234', '5678', $exception);

        $this->assertEquals(
            [
                'boxGoldenId' => '1234',
                'date' => '5678',
                'exception' => $exception,
            ],
            $event->getContext()
        );
    }
}
