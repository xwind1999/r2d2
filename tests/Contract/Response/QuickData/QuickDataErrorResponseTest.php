<?php

declare(strict_types=1);

namespace App\Tests\Contract\Response\QuickData;

use App\Contract\Response\QuickData\QuickDataErrorResponse;
use App\Tests\ProphecyTestCase;

class QuickDataErrorResponseTest extends ProphecyTestCase
{
    public function testGetHttpCode()
    {
        $response = new QuickDataErrorResponse();
        $this->assertEquals(405, $response->getHttpCode());
    }
}
