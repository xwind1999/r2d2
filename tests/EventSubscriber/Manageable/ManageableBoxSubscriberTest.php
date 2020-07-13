<?php

declare(strict_types=1);

namespace App\Tests\EventSubscriber\Manageable;

use App\Event\Manageable\ManageableBoxEvent;
use App\EventSubscriber\Manageable\ManageableBoxSubscriber;
use App\Repository\BoxExperienceRepository;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @coversDefaultClass \App\EventSubscriber\Manageable\ManageableBoxSubscriber
 */
class ManageableBoxSubscriberTest extends TestCase
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
     * @var ManageableBoxEvent|ObjectProphecy
     */
    private $event;

    private ManageableBoxSubscriber $subscriber;

    public function setUp(): void
    {
        $this->logger = $this->prophesize(LoggerInterface::class);
        $this->repository = $this->prophesize(BoxExperienceRepository::class);
        $this->messageBus = $this->prophesize(MessageBusInterface::class);
        $this->event = $this->prophesize(ManageableBoxEvent::class);
        $this->subscriber = new ManageableBoxSubscriber(
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
                ManageableBoxEvent::class => ['handleMessage'],
            ],
            ManageableBoxSubscriber::getSubscribedEvents()
        );
    }

    /**
     * @covers ::__construct
     * @covers ::handleMessage
     * @covers \App\Contract\Request\BroadcastListener\ProductRequest::fromBox
     * @dataProvider experienceGoldenIdProvider
     */
    public function testHandleMessage(array $boxExperience): void
    {
        $this->repository
            ->findAllByBoxGoldenId($this->event->boxGoldenId)
            ->shouldBeCalledOnce()
            ->willReturn([$boxExperience])
        ;
        $this->messageBus->dispatch(Argument::any())->shouldBeCalledOnce()->willReturn(new Envelope(new \stdClass()));
        $this->logger->error(Argument::any(), Argument::any())->shouldNotBeCalled();
        $this->subscriber->handleMessage($this->event->reveal());
    }

    /**
     * @covers ::__construct
     * @covers ::handleMessage
     * @covers \App\Contract\Request\BroadcastListener\ProductRequest::fromBox
     * @dataProvider experienceGoldenIdProvider
     */
    public function testHandleMessageCatchesException(array $experienceGoldenId): void
    {
        $this->repository
            ->findAllByBoxGoldenId($this->event->boxGoldenId)
            ->shouldBeCalledOnce()
            ->willReturn([$experienceGoldenId])
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
    public function experienceGoldenIdProvider(): array
    {
        return [
            'list' => [
                0 => [
                    'experienceGoldenId' => '12345',
                ],
            ],
        ];
    }
}
