<?php

declare(strict_types=1);

namespace App\Tests\EventSubscriber\Manageable;

use App\Entity\Component;
use App\Entity\Partner;
use App\Event\Manageable\ManageableExperienceComponentEvent;
use App\EventSubscriber\Manageable\ManageableExperienceComponentSubscriber;
use App\Manager\ComponentManager;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @coversDefaultClass \App\EventSubscriber\Manageable\ManageableExperienceComponentSubscriber
 */
class ManageableExperienceComponentSubscriberTest extends TestCase
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
     * @var MessageBusInterface|ObjectProphecy
     */
    private $messageBus;

    /**
     * @var ManageableExperienceComponentEvent|ObjectProphecy
     */
    private $event;

    private ManageableExperienceComponentSubscriber $subscriber;

    public function setUp(): void
    {
        $this->logger = $this->prophesize(LoggerInterface::class);
        $this->manager = $this->prophesize(ComponentManager::class);
        $this->messageBus = $this->prophesize(MessageBusInterface::class);
        $this->event = $this->prophesize(ManageableExperienceComponentEvent::class);
        $this->subscriber = new ManageableExperienceComponentSubscriber(
            $this->logger->reveal(),
            $this->manager->reveal(),
            $this->messageBus->reveal()
        );
        $this->event->boxGoldenId = '12345';
    }

    /**
     * @covers ::getSubscribedEvents
     */
    public function testGetSubscribedEvents(): void
    {
        $this->assertEquals(
            [
                ManageableExperienceComponentEvent::class => ['handleMessage'],
            ],
            ManageableExperienceComponentSubscriber::getSubscribedEvents()
        );
    }

    /**
     * @covers ::__construct
     * @covers ::handleMessage
     * @covers \App\Contract\Request\BroadcastListener\ProductRequest::fromExperienceComponent
     * @dataProvider componentProvider
     */
    public function testHandleMessage(ObjectProphecy $component): void
    {
        $this->manager
            ->findAndSetManageableComponent(Argument::any())
            ->shouldBeCalledOnce()
            ->willReturn($component->reveal())
        ;
        $this->messageBus->dispatch(Argument::any())->shouldBeCalledOnce()->willReturn(new Envelope(new \stdClass()));
        $this->logger->error(Argument::any(), Argument::any())->shouldNotBeCalled();
        $this->subscriber->handleMessage($this->event->reveal());
    }

    /**
     * @covers ::__construct
     * @covers ::handleMessage
     * @covers \App\Contract\Request\BroadcastListener\ProductRequest::fromExperienceComponent
     * @dataProvider componentProvider
     */
    public function testHandleMessageCatchesException(ObjectProphecy $component): void
    {
        $this->manager
            ->findAndSetManageableComponent(Argument::any())
            ->shouldBeCalledOnce()
            ->willReturn($component->reveal())
        ;
        $this->messageBus->dispatch(Argument::any())->shouldBeCalledOnce()->willThrow(\Exception::class);
        $this->logger->error(Argument::any(), Argument::any())->shouldBeCalledOnce();
        $this->event->getContext()->shouldBeCalledOnce()->willReturn([
            'box_golden_id' => $this->event->boxGoldenId,
            'component_golden_id' => $this->event->componentGoldenId,
            'experience_golden_id' => $this->event->experienceGoldenId,
        ]);
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
