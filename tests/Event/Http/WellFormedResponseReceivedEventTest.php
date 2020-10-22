<?php

declare(strict_types=1);

namespace App\Tests\Event\Http;

use App\Event\Http\WellFormedResponseReceivedEvent;
use App\Tests\ProphecyTestCase;
use Symfony\Contracts\HttpClient\ResponseInterface;

class WellFormedResponseReceivedEventTest extends ProphecyTestCase
{
    public function testGetMessage()
    {
        $response = $this->prophesize(ResponseInterface::class);
        $event = new WellFormedResponseReceivedEvent('', '', '', [], 0, $response->reveal());
        $this->assertIsString($event->getMessage());
    }

    public function testGetContext()
    {
        $response = $this->prophesize(ResponseInterface::class);
        $response->getHeaders(false)->willReturn([]);
        $response->getStatusCode()->willReturn(200);
        $response->getInfo('url')->willReturn('');
        $response->getContent(false)->willReturn('');
        $event = new WellFormedResponseReceivedEvent('', '', '', [], 0, $response->reveal());

        $expected = [
            'request' => [
                'client' => '',
                'method' => '',
                'uri' => '',
                'options' => [],
                'duration' => 0,
            ],
            'response' => [
                'headers' => [],
                'status_code' => 200,
                'location' => '',
                'body' => '',
            ],
        ];
        $this->assertEquals($expected, $event->getContext());
    }
}
