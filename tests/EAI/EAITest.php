<?php

declare(strict_types=1);

namespace App\Tests\EAI;

use App\Contract\Request\EAI\RoomRequest;
use App\EAI\EAI;
use App\Tests\ProphecyTestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Smartbox\ApiRestClient\ApiRestResponse;
use Smartbox\ApiRestClient\Clients\EaiV0Client;
use Smartbox\CDM\Entity\Booking\ChannelManagerBooking;

/**
 * @coversDefaultClass \App\EAI\EAI
 */
class EAITest extends ProphecyTestCase
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

    /**
     * @covers ::__construct
     * @covers ::pushChannelManagerBooking
     */
    public function testPushChannelManagerBooking()
    {
        $channelManagerBooking = $this->prophesize(ChannelManagerBooking::class);
        $apiRestResponse = $this->prophesize(ApiRestResponse::class);
        $eai = new EAI($this->eaiClient->reveal());

        $this->eaiClient
            ->request('POST', Argument::any(), Argument::any(), Argument::any())
            ->shouldBeCalledOnce()
            ->willReturn($apiRestResponse->reveal())
        ;
        $eai->pushChannelManagerBooking($channelManagerBooking->reveal());
    }
}
