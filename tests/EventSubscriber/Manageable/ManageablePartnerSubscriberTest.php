<?php

declare(strict_types=1);

namespace App\Tests\EventSubscriber\Manageable;

use App\Entity\Component;
use App\Event\Manageable\ManageablePartnerEvent;
use App\EventSubscriber\Manageable\ManageablePartnerSubscriber;
use App\Repository\ComponentRepository;
use App\Repository\ExperienceRepository;
use App\Tests\ProphecyTestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @coversDefaultClass \App\EventSubscriber\Manageable\ManageablePartnerSubscriber
 */
class ManageablePartnerSubscriberTest extends ProphecyTestCase
{
    /**
     * @var LoggerInterface|ObjectProphecy
     */
    private $logger;

    /**
     * @var ExperienceRepository|ObjectProphecy
     */
    private $repository;

    /**
     * @var MessageBusInterface|ObjectProphecy
     */
    private $messageBus;

    /**
     * @var ManageablePartnerEvent|ObjectProphecy
     */
    private $event;

    private ManageablePartnerSubscriber $subscriber;

    public function setUp(): void
    {
        $this->logger = $this->prophesize(LoggerInterface::class);
        $this->repository = $this->prophesize(ComponentRepository::class);
        $this->messageBus = $this->prophesize(MessageBusInterface::class);
        $this->event = $this->prophesize(ManageablePartnerEvent::class);
        $this->subscriber = new ManageablePartnerSubscriber(
            $this->logger->reveal(),
            $this->repository->reveal(),
            $this->messageBus->reveal()
        );
        $this->event->partnerGoldenId = '12345';
    }

    /**
     * @covers ::getSubscribedEvents
     */
    public function testGetSubscribedEvents(): void
    {
        $this->assertEquals(
            [
                ManageablePartnerEvent::class => ['handleMessage'],
            ],
            ManageablePartnerSubscriber::getSubscribedEvents()
        );
    }

    /**
     * @covers ::__construct
     * @covers ::handleMessage
     * @dataProvider componentProvider
     */
    public function testHandleMessage(array $componentList): void
    {
        $this->repository
            ->findListByPartner($this->event->partnerGoldenId)
            ->shouldBeCalledOnce()
            ->willReturn($componentList)
        ;
        $this->messageBus
            ->dispatch(Argument::any())
            ->shouldBeCalledTimes(count($componentList))
            ->willReturn(new Envelope(new \stdClass()))
        ;
        $this->logger->error(Argument::any(), Argument::any())->shouldNotBeCalled();
        $this->subscriber->handleMessage($this->event->reveal());
    }

    /**
     * @covers ::__construct
     * @covers ::handleMessage
     * @dataProvider componentProvider
     */
    public function testHandleMessageCatchesException(array $componentList): void
    {
        $this->repository
            ->findListByPartner($this->event->partnerGoldenId)
            ->shouldBeCalledOnce()
            ->willReturn($componentList)
        ;
        $this->messageBus->dispatch(Argument::any())->shouldBeCalledOnce()->willThrow(\Exception::class);
        $this->logger->error(Argument::any(), Argument::any())->shouldBeCalledOnce();
        $this->event->getContext()->shouldBeCalledOnce()->willReturn([
            'box_golden_id' => $this->event->boxGoldenId,
            'component_golden_id' => $this->event->componentGoldenId,
            'experience_golden_id' => $this->event->experienceGoldenId,
            'partner_golden_id' => $this->event->partnerGoldenId,
        ]);
        $this->subscriber->handleMessage($this->event->reveal());
    }

    /**
     * @see testHandleMessage
     * @see testHandleMessageCatchesException
     */
    public function componentProvider(): array
    {
        $component = $this->prophesize(Component::class);
        $component->goldenId = '12345';
        $component->name = 'name';
        $component->status = 'active';

        return [[[$component->reveal()]]];
    }
}
