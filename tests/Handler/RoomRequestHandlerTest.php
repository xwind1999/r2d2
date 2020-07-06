<?php

declare(strict_types=1);

namespace App\Tests\Handler;

use App\Contract\Request\EAI\RoomRequest;
use App\EAI\EAI;
use App\Handler\RoomRequestHandler;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;
use Smartbox\ApiRestClient\ApiRestException;
use Smartbox\CDM\Entity\Partner\Partner;
use Smartbox\CDM\Entity\Product\Product;

/**
 * @coversDefaultClass \App\Handler\RoomRequestHandler
 */
class RoomRequestHandlerTest extends TestCase
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
     * @var ObjectProphecy|RoomRequest
     */
    private $roomRequest;

    private RoomRequestHandler $roomRequestHandler;

    protected function setUp(): void
    {
        $this->eaiProvider = $this->prophesize(EAI::class);
        $this->logger = $this->prophesize(LoggerInterface::class);
        $this->roomRequest = $this->prophesize(RoomRequest::class);
        $this->roomRequestHandler = new RoomRequestHandler(
            $this->eaiProvider->reveal(),
            $this->logger->reveal(),
        );
    }

    /**
     * @covers ::__construct
     * @covers ::__invoke
     * @covers \App\Contract\Request\EAI\RoomRequest::getContext
     */
    public function testHandlerMessageSuccessfully(): void
    {
        $product = $this->prophesize(Product::class);
        $product->setId('12345');
        $product->setName('name');
        $product->setIsSellable(true);
        $product->setDescription('description');
        $product->setRoomStockType('2');
        $partner = $this->prophesize(Partner::class);
        $partner->setId('54321');
        $product->setPartner($partner->reveal());
        $this->roomRequest->setIsActive(true);
        $this->roomRequest->setProduct($product);
        $this->logger->error(Argument::any())->shouldNotBeCalled();
        $this->eaiProvider->pushRoom(Argument::any())->shouldBeCalledOnce();
        $this->roomRequest->getContext()->shouldBeCalledOnce();
        $this->logger->info('Room pushed to EAI', Argument::any())->shouldBeCalledOnce();

        $this->roomRequestHandler->__invoke($this->roomRequest->reveal());
    }

    /**
     * @covers ::__construct
     * @covers ::__invoke
     */
    public function testHandlerMessageThrowsException(): void
    {
        $this->eaiProvider->pushRoom(Argument::any())->shouldBeCalledOnce()->willThrow(ApiRestException::class);
        $this->logger->info(Argument::any(), Argument::any())->shouldNotBeCalled();
        $this->roomRequest->getContext()->shouldBeCalledOnce();
        $this->logger->error(Argument::any(), Argument::any())->shouldBeCalledOnce();

        $this->roomRequestHandler->__invoke($this->roomRequest->reveal());
    }
}
