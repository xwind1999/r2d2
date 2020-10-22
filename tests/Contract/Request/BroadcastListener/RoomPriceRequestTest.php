<?php

declare(strict_types=1);

namespace App\Tests\Contract\Request\BroadcastListener;

use App\Contract\Request\BroadcastListener\RoomPriceRequest;
use App\Tests\ProphecyTestCase;

class RoomPriceRequestTest extends ProphecyTestCase
{
    public function testGetEventName()
    {
        $request = new RoomPriceRequest();
        $this->assertEquals('Room price broadcast', $request->getEventName());
    }
}
