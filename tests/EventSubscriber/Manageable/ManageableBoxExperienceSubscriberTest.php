<?php

declare(strict_types=1);

namespace App\Tests\EventSubscriber\Manageable;

use App\Event\Manageable\ManageableBoxExperienceEvent;
use App\EventSubscriber\Manageable\ManageableBoxExperienceSubscriber;
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
        $this->messageBus = $this->prophesize(MessageBusInterface::class);
        $this->event = $this->prophesize(ManageableBoxExperienceEvent::class);
        $this->subscriber = new ManageableBoxExperienceSubscriber(
            $this->logger->reveal(),
            $this->messageBus->reveal()
        );
        $this->event->experienceGoldenId = '12345';
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
     */
    public function testHandleMessage(): void
    {
        $this->messageBus->dispatch(Argument::any())->shouldBeCalledOnce()->willReturn(new Envelope(new \stdClass()));
        $this->logger->error(Argument::any(), Argument::any())->shouldNotBeCalled();
        $this->subscriber->handleMessage($this->event->reveal());
    }

    /**
     * @covers ::__construct
     * @covers ::handleMessage
     * @covers \App\Contract\Request\BroadcastListener\ProductRequest::fromBoxExperience
     */
    public function testHandleMessageCatchesException(): void
    {
        $this->messageBus->dispatch(Argument::any())->shouldBeCalledOnce()->willThrow(\Exception::class);
        $this->logger->error(Argument::any(), Argument::any())->shouldBeCalledOnce();
        $this->event->getContext()->shouldBeCalledOnce()->willReturn([
            'box_golden_id' => $this->event->boxGoldenId,
            'component_golden_id' => $this->event->componentGoldenId,
            'experience_golden_id' => $this->event->experienceGoldenId,
        ]);
        $this->subscriber->handleMessage($this->event->reveal());
    }
}
