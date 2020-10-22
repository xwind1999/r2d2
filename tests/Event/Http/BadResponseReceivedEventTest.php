<?php

declare(strict_types=1);

namespace App\Tests\Event\Http;

use App\Event\Http\BadResponseReceivedEvent;
use App\Tests\ProphecyTestCase;
use Symfony\Contracts\HttpClient\ResponseInterface;

class BadResponseReceivedEventTest extends ProphecyTestCase
{
    public function testGetMessage()
    {
        $event = new BadResponseReceivedEvent('', '', '', [], 0, null);
        $this->assertIsString($event->getMessage());
    }

    public function testGetContextWithNullResponse()
    {
        $event = new BadResponseReceivedEvent('', '', '', [], 0, null);
        $expected = [
            'request' => [
                'client' => '',
                'method' => '',
                'uri' => '',
                'options' => [],
                'duration' => 0,
            ],
            'response' => [],
        ];
        $this->assertEquals($expected, $event->getContext());
    }

    public function testGetContextWithProperResponse()
    {
        $response = $this->prophesize(ResponseInterface::class);
        $response->getHeaders(false)->willReturn([]);
        $response->getStatusCode()->willReturn(200);
        $response->getInfo('url')->willReturn('');
        $response->getContent(false)->willReturn('');
        $event = new BadResponseReceivedEvent('', '', '', [], 0, $response->reveal());

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
