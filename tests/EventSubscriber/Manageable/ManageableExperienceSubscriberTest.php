<?php

declare(strict_types=1);

namespace App\Tests\EventSubscriber\Manageable;

use App\Entity\Component;
use App\Entity\Experience;
use App\Entity\ExperienceComponent;
use App\Event\Manageable\ManageableExperienceEvent;
use App\EventSubscriber\Manageable\ManageableExperienceSubscriber;
use App\Repository\ExperienceComponentRepository;
use App\Tests\ProphecyTestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @coversDefaultClass \App\EventSubscriber\Manageable\ManageableExperienceSubscriber
 */
class ManageableExperienceSubscriberTest extends ProphecyTestCase
{
    /**
     * @var LoggerInterface|ObjectProphecy
     */
    private $logger;

    /**
     * @var ExperienceComponentRepository|ObjectProphecy
     */
    private $repository;
    /**
     * @var MessageBusInterface|ObjectProphecy
     */
    private $messageBus;

    /**
     * @var ManageableExperienceEvent|ObjectProphecy
     */
    private $event;

    private ManageableExperienceSubscriber $subscriber;

    public function setUp(): void
    {
        $this->logger = $this->prophesize(LoggerInterface::class);
        $this->repository = $this->prophesize(ExperienceComponentRepository::class);
        $this->messageBus = $this->prophesize(MessageBusInterface::class);
        $this->event = $this->prophesize(ManageableExperienceEvent::class);
        $this->subscriber = new ManageableExperienceSubscriber(
            $this->logger->reveal(),
            $this->repository->reveal(),
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
                ManageableExperienceEvent::class => ['handleMessage'],
            ],
            ManageableExperienceSubscriber::getSubscribedEvents()
        );
    }

    /**
     * @covers ::__construct
     * @covers ::handleMessage
     * @covers \App\Contract\Request\BroadcastListener\ProductRequest::fromExperienceComponent
     * @dataProvider experienceComponentProvider
     */
    public function testHandleMessage(ObjectProphecy $boxExperience): void
    {
        $this->repository
            ->findBy(['experienceGoldenId' => $this->event->experienceGoldenId])
            ->shouldBeCalledOnce()
            ->willReturn([$boxExperience->reveal()])
        ;
        $this->messageBus->dispatch(Argument::any())->shouldBeCalledOnce()->willReturn(new Envelope(new \stdClass()));
        $this->logger->error(Argument::any(), Argument::any())->shouldNotBeCalled();
        $this->subscriber->handleMessage($this->event->reveal());
    }

    /**
     * @covers ::__construct
     * @covers ::handleMessage
     * @covers \App\Contract\Request\BroadcastListener\ProductRequest::fromExperienceComponent
     * @dataProvider experienceComponentProvider
     */
    public function testHandleMessageCatchesException(ObjectProphecy $boxExperience): void
    {
        $this->repository
            ->findBy(['experienceGoldenId' => $this->event->experienceGoldenId])
            ->shouldBeCalledOnce()
            ->willReturn([$boxExperience->reveal()])
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
    public function experienceComponentProvider(): \Generator
    {
        $component = $this->prophesize(Component::class);
        $component->goldenId = '12345';
        $component->name = 'name';
        $experience = $this->prophesize(Experience::class);
        $experience->status = 'active';
        $experienceComponent = $this->prophesize(ExperienceComponent::class);
        $experienceComponent->component = $component->reveal();
        $experienceComponent->experience = $experience->reveal();

        yield [
            'manageable-product' => $experienceComponent,
        ];
    }
}
