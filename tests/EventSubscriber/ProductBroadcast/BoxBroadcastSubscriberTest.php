<?php

declare(strict_types=1);

namespace App\Tests\EventSubscriber\ProductBroadcast;

use App\Contract\Request\BroadcastListener\Product\Brand;
use App\Contract\Request\BroadcastListener\Product\Country;
use App\Contract\Request\BroadcastListener\Product\Partner;
use App\Contract\Request\BroadcastListener\Product\Universe;
use App\Contract\Request\BroadcastListener\ProductRequest;
use App\Event\Product\BoxBroadcastEvent;
use App\EventSubscriber\ProductBroadcast\BoxBroadcastSubscriber;
use App\Exception\Manager\Box\OutdatedBoxException;
use App\Manager\BoxManager;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;

/**
 * @coversDefaultClass \App\EventSubscriber\ProductBroadcast\BoxBroadcastSubscriber
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
            [BoxBroadcastEvent::class => ['handleMessage']],
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
        $partner = Partner::create('4321');
        $universe = Universe::create('universe');
        $country = Country::create('FR');
        $brand = Brand::create('SBX');
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

        $this->boxEvent->getProductRequest()->shouldBeCalled()->willReturn($productRequest);
        $this->boxManager->replace($productRequest)->shouldBeCalled()->willThrow(new \Exception());
        $boxSubscriber = new BoxBroadcastSubscriber($this->logger->reveal(), $this->boxManager->reveal());
        $this->logger->error(
            '',
            [
                'id' => '1234',
                'name' => 'box name',
                'description' => 'Lorem ipsum',
                'universe' => 'universe',
                'is_sellable' => true,
                'is_reservable' => true,
                'partner' => '4321',
                'sellable_brand' => 'SBX',
                'sellable_country' => 'FR',
                'status' => 'active',
                'type' => 'mev',
                'product_people_number' => null,
                'product_duration' => null,
                'product_duration_unit' => null,
                'room_stock_type' => null,
                'stock_allotment' => null,
                'list_price' => null,
                'updated_at' => null,
            ]
        )->shouldBeCalled();
        $this->expectException(\Exception::class);
        $this->assertEmpty($boxSubscriber->handleMessage($this->boxEvent->reveal()));
    }

    /**
     * @covers ::__construct
     * @covers ::handleMessage
     */
    public function testHandleMessageCatchesOutdatedException(): void
    {
        $productRequest = new ProductRequest();

        $this->boxEvent->getProductRequest()->shouldBeCalled()->willReturn($productRequest);
        $this->boxEvent->getContext()->willReturn(['aaa' => 'bbb']);

        $exception = new OutdatedBoxException();
        $this->boxManager->replace($productRequest)->shouldBeCalled()->willThrow($exception);
        $boxSubscriber = new BoxBroadcastSubscriber($this->logger->reveal(), $this->boxManager->reveal());

        $this->logger->warning($exception, ['aaa' => 'bbb'])->shouldBeCalled();

        $this->expectException(OutdatedBoxException::class);
        $boxSubscriber->handleMessage($this->boxEvent->reveal());
    }
}
