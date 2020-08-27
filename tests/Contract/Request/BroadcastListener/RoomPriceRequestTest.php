<?php

declare(strict_types=1);

namespace App\Tests\Contract\Request\BroadcastListener;

use App\Contract\Request\BroadcastListener\RoomPriceRequest;
use PHPUnit\Framework\TestCase;

class RoomPriceRequestTest extends TestCase
{
    public function testGetEventName()
    {
        $request = new RoomPriceRequest();
        $this->assertEquals('Room price broadcast', $request->getEventName());
    }
}
