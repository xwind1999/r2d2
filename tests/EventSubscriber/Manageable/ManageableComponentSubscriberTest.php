<?php

declare(strict_types=1);

namespace App\Tests\EventSubscriber\Manageable;

use App\Entity\Component;
use App\Entity\Partner;
use App\Event\Manageable\ManageableComponentEvent;
use App\EventSubscriber\Manageable\ManageableComponentSubscriber;
use App\Manager\ComponentManager;
use App\Tests\ProphecyTestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;

/**
 * @coversDefaultClass \App\EventSubscriber\Manageable\ManageableComponentSubscriber
 */
class ManageableComponentSubscriberTest extends ProphecyTestCase
{
    /**
     * @var LoggerInterface|ObjectProphecy
     */
    private $logger;

    /**
     * @var ComponentManager|ObjectProphecy
     */
    private $manager;

    /**
     * @var ManageableComponentEvent|ObjectProphecy
     */
    private $event;

    private ManageableComponentSubscriber $subscriber;

    public function setUp(): void
    {
        $this->logger = $this->prophesize(LoggerInterface::class);
        $this->manager = $this->prophesize(ComponentManager::class);
        $this->event = $this->prophesize(ManageableComponentEvent::class);
        $this->event->boxGoldenId = '12345';
        $this->subscriber = new ManageableComponentSubscriber(
            $this->logger->reveal(),
            $this->manager->reveal()
        );
    }

    /**
     * @covers ::getSubscribedEvents
     */
    public function testGetSubscribedEvents(): void
    {
        $this->assertEquals(
            [
                ManageableComponentEvent::class => ['handleMessage'],
            ],
            ManageableComponentSubscriber::getSubscribedEvents()
        );
    }

    /**
     * @covers ::__construct
     * @covers ::handleMessage
     * @covers \App\Contract\Request\BroadcastListener\ProductRequest::fromComponent
     * @dataProvider componentProvider
     */
    public function testHandleMessage(ObjectProphecy $component): void
    {
        $this->manager
            ->calculateManageableFlag(Argument::any())
            ->shouldBeCalledOnce()
        ;
        $this->logger->error(Argument::any(), Argument::any())->shouldNotBeCalled();
        $this->subscriber->handleMessage($this->event->reveal());
    }

    /**
     * @covers ::__construct
     * @covers ::handleMessage
     * @covers \App\Contract\Request\BroadcastListener\ProductRequest::fromComponent
     * @dataProvider componentProvider
     */
    public function testHandleMessageCatchesException(ObjectProphecy $component): void
    {
        $this->manager
            ->calculateManageableFlag(Argument::any())
            ->willThrow(new \Exception())
        ;
        $exception = \Exception::class;
        $this->logger->error(Argument::any(), Argument::any())->shouldBeCalledOnce();
        $this->event->getContext()->shouldBeCalledOnce()->willReturn([
            'box_golden_id' => $this->event->boxGoldenId,
            'component_golden_id' => $this->event->componentGoldenId,
            'experience_golden_id' => $this->event->experienceGoldenId,
        ]);
        $this->expectException($exception);
        $this->subscriber->handleMessage($this->event->reveal());
    }

    /**
     * @see testHandleMessage
     * @see testHandleMessageCatchesException
     */
    public function componentProvider(): \Generator
    {
        $component = $this->prophesize(Component::class);
        $component->goldenId = '12345';
        $component->name = 'name';
        $component->isSellable = true;
        $component->isManageable = true;
        $component->description = 'description';
        $component->roomStockType = 'allotment';
        $partner = $this->prophesize(Partner::class);
        $partner->goldenId = '78945';
        $component->partner = $partner->reveal();

        yield [
            'manageable-product' => $component,
        ];
    }
}
