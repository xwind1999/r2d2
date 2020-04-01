<?php

declare(strict_types=1);

namespace App\Tests\Event\Http;

use App\Event\Http\ExternalServiceRequestMadeEvent;
use PHPUnit\Framework\TestCase;

class ExternalServiceRequestMadeEventTest extends TestCase
{
    public function testGetMessage()
    {
        $event = new ExternalServiceRequestMadeEvent('', '', '', []);
        $this->assertIsString($event->getMessage());
    }

    public function testGetContext()
    {
        $event = new ExternalServiceRequestMadeEvent('', '', '', []);
        $expected = [
            'request' => [
                'client' => '',
                'method' => '',
                'uri' => '',
                'options' => [],
            ],
        ];
        $this->assertEquals($expected, $event->getContext());
    }
}
