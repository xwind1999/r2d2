<?php

declare(strict_types=1);

namespace App\Tests\EAI;

use App\Contract\Request\EAI\RoomRequest;
use App\EAI\EAI;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Smartbox\ApiRestClient\ApiRestResponse;
use Smartbox\ApiRestClient\Clients\EaiV0Client;

/**
 * @coversDefaultClass \App\EAI\EAI
 */
class EAITest extends TestCase
{
    /**
     * @var EaiV0Client|ObjectProphecy
     */
    protected $eaiClient;

    public function setUp(): void
    {
        $this->eaiClient = $this->prophesize(EaiV0Client::class);
    }

    /**
     * @covers ::__construct
     * @covers ::pushRoom
     * @covers \App\Contract\Request\EAI\RoomRequest::transformFromComponent
     */
    public function testPushRoom()
    {
        $roomRequest = $this->prophesize(RoomRequest::class);
        $apiRestResponse = $this->prophesize(ApiRestResponse::class);
        $eai = new EAI($this->eaiClient->reveal());

        $this->eaiClient
            ->sendRoomTypeProductInformation(Argument::any())
            ->shouldBeCalledOnce()
            ->willReturn($apiRestResponse->reveal())
        ;
        $eai->pushRoom($roomRequest->reveal());
    }
}
