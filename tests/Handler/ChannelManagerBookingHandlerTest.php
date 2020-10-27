<?php

declare(strict_types=1);

namespace App\Tests\Handler;

use App\Contract\Request\EAI\ChannelManagerBookingRequest;
use App\EAI\EAI;
use App\Handler\ChannelManagerBookingHandler;
use App\Tests\ProphecyTestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;
use Smartbox\ApiRestClient\ApiRestException;

/**
 * @coversDefaultClass \App\Handler\ChannelManagerBookingHandler
 */
class ChannelManagerBookingHandlerTest extends ProphecyTestCase
{
    /**
     * @var EAI|ObjectProphecy
     */
    private $eaiProvider;

    /**
     * @var LoggerInterface|ObjectProphecy
     */
    private $logger;
    /**
     * @var ChannelManagerBookingRequest|ObjectProphecy
     */
    private $channelManagerBookingRequest;

    private ChannelManagerBookingHandler $channelManagerBookingHandler;

    protected function setUp(): void
    {
        $this->eaiProvider = $this->prophesize(EAI::class);
        $this->logger = $this->prophesize(LoggerInterface::class);
        $this->channelManagerBookingRequest = $this->prophesize(ChannelManagerBookingRequest::class);
        $this->channelManagerBookingHandler = new ChannelManagerBookingHandler(
            $this->logger->reveal(),
            $this->eaiProvider->reveal(),
        );
    }

    /**
     * @covers ::__construct
     * @covers ::__invoke
     * @covers \App\Contract\Request\EAI\ChannelManagerBookingRequest::getStatus
     * @covers \App\Contract\Request\EAI\ChannelManagerBookingRequest::getContext
     */
    public function testHandlerMessageSuccessfully(): void
    {
        $this->eaiProvider->pushChannelManagerBooking(Argument::any())->shouldBeCalledOnce();
        $this->logger->error(Argument::any(), Argument::any())->shouldNotBeCalled();

        $this->channelManagerBookingHandler->__invoke($this->channelManagerBookingRequest->reveal());
    }

    /**
     * @covers ::__construct
     * @covers ::__invoke
     * @covers \App\Contract\Request\EAI\ChannelManagerBookingRequest::getContext
     */
    public function testHandlerMessageThrowsException(): void
    {
        $this->eaiProvider
            ->pushChannelManagerBooking(Argument::any())
            ->shouldBeCalledOnce()
            ->willThrow(ApiRestException::class)
        ;
        $this->channelManagerBookingRequest->getContext()->shouldBeCalledOnce();
        $this->logger->error(Argument::any(), Argument::any())->shouldBeCalledOnce();
        $this->expectException(ApiRestException::class);

        $this->channelManagerBookingHandler->__invoke($this->channelManagerBookingRequest->reveal());
    }
}
