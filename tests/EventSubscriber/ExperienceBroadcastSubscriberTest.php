<?php

declare(strict_types=1);

namespace App\Tests\EventSubscriber;

use App\Contract\Request\BroadcastListener\Product\Brand;
use App\Contract\Request\BroadcastListener\Product\Country;
use App\Contract\Request\BroadcastListener\Product\Partner;
use App\Contract\Request\BroadcastListener\Product\Universe;
use App\Contract\Request\BroadcastListener\ProductRequest;
use App\Event\Product\ExperienceBroadcastEvent;
use App\EventSubscriber\ExperienceBroadcastSubscriber;
use App\Manager\ExperienceManager;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;

/**
 * @coversDefaultClass \App\EventSubscriber\ExperienceBroadcastSubscriber
 */
class ExperienceBroadcastSubscriberTest extends TestCase
{
    /**
     * @var LoggerInterface|ObjectProphecy
     */
    private $logger;

    /**
     * @var ExperienceManager|ObjectProphecy
     */
    private $experienceManager;

    /**
     * @var ExperienceBroadcastEvent|ObjectProphecy
     */
    private $experienceEvent;

    public function setUp(): void
    {
        $this->logger = $this->prophesize(LoggerInterface::class);
        $this->experienceManager = $this->prophesize(ExperienceManager::class);
        $this->experienceEvent = $this->prophesize(ExperienceBroadcastEvent::class);
    }

    /**
     * @covers ::getSubscribedEvents
     */
    public function testGetSubscribedEvents(): void
    {
        $this->assertEquals(
            [ExperienceBroadcastEvent::class => ['handleMessage']],
            ExperienceBroadcastSubscriber::getSubscribedEvents()
        );
    }

    /**
     * @covers ::__construct
     * @covers ::handleMessage
     */
    public function testHandleMessage(): void
    {
        $this->experienceEvent->getProductRequest()->shouldBeCalled()->willReturn(new ProductRequest());
        $this->experienceManager->replace(new ProductRequest())->shouldBeCalled();

        $experienceSubscriber = new ExperienceBroadcastSubscriber($this->logger->reveal(), $this->experienceManager->reveal());

        $this->assertEmpty($experienceSubscriber->handleMessage($this->experienceEvent->reveal()));
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
        $productRequest->name = 'experience name';
        $productRequest->description = 'Lorem ipsum';
        $productRequest->universe = $universe;
        $productRequest->isReservable = true;
        $productRequest->isSellable = true;
        $productRequest->sellableCountry = $country;
        $productRequest->sellableBrand = $brand;
        $productRequest->status = 'active';
        $productRequest->type = 'mev';

        $this->experienceEvent->getProductRequest()->shouldBeCalled()->willReturn($productRequest);

        $this->experienceManager->replace($productRequest)->shouldBeCalled()->willThrow(new \Exception());
        $experienceSubscriber = new ExperienceBroadcastSubscriber($this->logger->reveal(), $this->experienceManager->reveal());

        $this->assertEmpty($experienceSubscriber->handleMessage($this->experienceEvent->reveal()));
    }
}
