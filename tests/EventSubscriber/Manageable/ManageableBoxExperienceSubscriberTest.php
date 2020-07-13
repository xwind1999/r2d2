<?php

declare(strict_types=1);

namespace App\Tests\EventSubscriber\Manageable;

use App\Entity\BoxExperience;
use App\Entity\Experience;
use App\Event\Manageable\ManageableBoxExperienceEvent;
use App\EventSubscriber\Manageable\ManageableBoxExperienceSubscriber;
use App\Repository\BoxExperienceRepository;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @coversDefaultClass \App\EventSubscriber\Manageable\ManageableBoxExperienceSubscriber
 */
class ManageableBoxExperienceSubscriberTest extends TestCase
{
    /**
     * @var LoggerInterface|ObjectProphecy
     */
    private $logger;

    /**
     * @var BoxExperienceRepository|ObjectProphecy
     */
    private $repository;
    /**
     * @var MessageBusInterface|ObjectProphecy
     */
    private $messageBus;

    /**
     * @var ManageableBoxExperienceEvent|ObjectProphecy
     */
    private $event;

    private ManageableBoxExperienceSubscriber $subscriber;

    public function setUp(): void
    {
        $this->logger = $this->prophesize(LoggerInterface::class);
        $this->repository = $this->prophesize(BoxExperienceRepository::class);
        $this->messageBus = $this->prophesize(MessageBusInterface::class);
        $this->event = $this->prophesize(ManageableBoxExperienceEvent::class);
        $this->subscriber = new ManageableBoxExperienceSubscriber(
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
                ManageableBoxExperienceEvent::class => ['handleMessage'],
            ],
            ManageableBoxExperienceSubscriber::getSubscribedEvents()
        );
    }

    /**
     * @covers ::__construct
     * @covers ::handleMessage
     * @covers \App\Contract\Request\BroadcastListener\ProductRequest::fromBoxExperience
     * @dataProvider boxExperienceProvider
     */
    public function testHandleMessage(ObjectProphecy $boxExperience): void
    {
        $this->repository
            ->findBy(['boxGoldenId' => $this->event->boxGoldenId])
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
     * @covers \App\Contract\Request\BroadcastListener\ProductRequest::fromBoxExperience
     * @dataProvider boxExperienceProvider
     */
    public function testHandleMessageCatchesException(ObjectProphecy $boxExperience): void
    {
        $this->repository
            ->findBy(['boxGoldenId' => $this->event->boxGoldenId])
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
    public function boxExperienceProvider(): \Generator
    {
        $experience = $this->prophesize(Experience::class);
        $experience->goldenId = '12345';
        $experience->name = 'name';
        $experience->status = 'active';
        $boxExperience = $this->prophesize(BoxExperience::class);
        $boxExperience->experience = $experience->reveal();

        yield [
            'manageable-product' => $boxExperience,
        ];
    }
}
