<?php

declare(strict_types=1);

namespace App\Tests\EventSubscriber;

use App\Contract\Request\BroadcastListener\ProductRequest;
use App\Event\Product\BoxBroadcastEvent;
use App\EventSubscriber\BoxBroadcastSubscriber;
use App\Manager\BoxManager;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;

/**
 * @coversDefaultClass \App\EventSubscriber\BoxBroadcastSubscriber
 */
class BoxBroadcastSubscriberTest extends TestCase
{
    /**
     * @var LoggerInterface|ObjectProphecy
     */
    private $logger;

    /**
     * @var BoxManager|ObjectProphecy
     */
    private $boxManager;

    /**
     * @var BoxBroadcastEvent|ObjectProphecy
     */
    private $boxEvent;

    public function setUp(): void
    {
        $this->logger = $this->prophesize(LoggerInterface::class);
        $this->boxManager = $this->prophesize(BoxManager::class);
        $this->boxEvent = $this->prophesize(BoxBroadcastEvent::class);
    }

    /**
     * @covers ::getSubscribedEvents
     */
    public function testGetSubscribedEvents(): void
    {
        $this->assertEquals(
            [BoxBroadcastEvent::EVENT_NAME => ['handleMessage']],
            BoxBroadcastSubscriber::getSubscribedEvents()
        );
    }

    /**
     * @covers ::__construct
     * @covers ::handleMessage
     */
    public function testHandleMessage(): void
    {
        $this->boxEvent->getProductRequest()->shouldBeCalled()->willReturn(new ProductRequest());
        $this->boxManager->replace(new ProductRequest())->shouldBeCalled();

        $boxSubscriber = new BoxBroadcastSubscriber($this->logger->reveal(), $this->boxManager->reveal());

        $this->assertEmpty($boxSubscriber->handleMessage($this->boxEvent->reveal()));
    }

    /**
     * @covers ::__construct
     * @covers ::handleMessage
     */
    public function testHandleMessageCatchesException(): void
    {
        $productRequest = new ProductRequest();
        $productRequest->goldenId = '1234';
        $productRequest->partnerGoldenId = '4321';
        $productRequest->name = 'box name';
        $productRequest->description = 'Lorem ipsum';
        $productRequest->universe = 'universe';
        $productRequest->isReservable = true;
        $productRequest->isSellable = true;
        $productRequest->country = 'FR';
        $productRequest->brand = 'SBX';
        $productRequest->status = 'active';
        $productRequest->type = 'mev';

        $this->boxEvent->getProductRequest()->shouldBeCalled()->willReturn($productRequest);

        $this->boxManager->replace($productRequest)->shouldBeCalled()->willThrow(new \Exception());
        $boxSubscriber = new BoxBroadcastSubscriber($this->logger->reveal(), $this->boxManager->reveal());

        $this->assertEmpty($boxSubscriber->handleMessage($this->boxEvent->reveal()));
    }
}
