<?php

declare(strict_types=1);

namespace App\Tests\EventSubscriber;

use App\Contract\Request\BroadcastListener\Product\Brand;
use App\Contract\Request\BroadcastListener\Product\Country;
use App\Contract\Request\BroadcastListener\Product\Partner;
use App\Contract\Request\BroadcastListener\Product\Universe;
use App\Contract\Request\BroadcastListener\ProductRequest;
use App\Event\Product\ComponentBroadcastEvent;
use App\EventSubscriber\ComponentBroadcastSubscriber;
use App\Manager\RoomManager;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;

/**
 * @coversDefaultClass \App\EventSubscriber\ComponentBroadcastSubscriber
 */
class ComponentBroadcastSubscriberTest extends TestCase
{
    /**
     * @var LoggerInterface|ObjectProphecy
     */
    private $logger;

    /**
     * @var ObjectProphecy|RoomManager
     */
    private $manager;

    /**
     * @var ComponentBroadcastEvent|ObjectProphecy
     */
    private $event;

    public function setUp(): void
    {
        $this->logger = $this->prophesize(LoggerInterface::class);
        $this->manager = $this->prophesize(RoomManager::class);
        $this->event = $this->prophesize(ComponentBroadcastEvent::class);
    }

    /**
     * @covers ::getSubscribedEvents
     */
    public function testGetSubscribedEvents(): void
    {
        $this->assertEquals(
            [ComponentBroadcastEvent::EVENT_NAME => ['handleMessage']],
            ComponentBroadcastSubscriber::getSubscribedEvents()
        );
    }

    /**
     * @covers ::__construct
     * @covers ::handleMessage
     */
    public function testHandleMessage(): void
    {
        $this->event->getProductRequest()->shouldBeCalled()->willReturn(new ProductRequest());
        $this->manager->replace(new ProductRequest())->shouldBeCalled();

        $subscriber = new ComponentBroadcastSubscriber($this->logger->reveal(), $this->manager->reveal());

        $this->assertEmpty($subscriber->handleMessage($this->event->reveal()));
    }

    /**
     * @covers ::__construct
     * @covers ::handleMessage
     */
    public function testHandleMessageCatchesException(): void
    {
        $partner = new Partner();
        $partner->id = '4321';
        $universe = new Universe();
        $universe->id = 'universe';
        $country = new Country();
        $country->code = 'FR';
        $brand = new Brand();
        $brand->code = 'SBX';
        $productRequest = new ProductRequest();
        $productRequest->id = '1234';
        $productRequest->partner = $partner;
        $productRequest->name = 'box name';
        $productRequest->description = 'Lorem ipsum';
        $productRequest->universe = $universe;
        $productRequest->isReservable = true;
        $productRequest->isSellable = true;
        $productRequest->sellableCountry = $country;
        $productRequest->sellableBrand = $brand;
        $productRequest->status = 'active';
        $productRequest->type = 'mev';
        $productRequest->voucherExpirationDuration = 3;

        $this->event->getProductRequest()->shouldBeCalled()->willReturn($productRequest);

        $this->manager->replace($productRequest)->shouldBeCalled()->willThrow(new \Exception());
        $subscriber = new ComponentBroadcastSubscriber($this->logger->reveal(), $this->manager->reveal());

        $this->assertEmpty($subscriber->handleMessage($this->event->reveal()));
    }
}
