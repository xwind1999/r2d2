<?php

declare(strict_types=1);

namespace App\Tests\EventSubscriber;

use App\Event\NamedEventInterface;
use App\EventSubscriber\NamedEventSubscriber;
use App\Tests\ProphecyTestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\EventDispatcher\Event;

class NamedEventSubscriberTest extends ProphecyTestCase
{
    /**
     * @var LoggerInterface|ObjectProphecy
     */
    private $logger;

    private NamedEventSubscriber $eventSubscriber;

    public function setUp(): void
    {
        $this->logger = $this->prophesize(LoggerInterface::class);
        $this->eventSubscriber = new NamedEventSubscriber($this->logger->reveal());
    }

    public function testGetSubscribedEvents(): void
    {
        $this->assertEquals(
            [NamedEventInterface::class => ['handleMessage']],
            NamedEventSubscriber::getSubscribedEvents()
        );
    }

    public function testHandleMessageWithNamedEvent()
    {
        $event = new class() extends Event implements NamedEventInterface {
            public function getEventName(): string
            {
                return 'event_name';
            }

            public function getContext(): array
            {
                return [];
            }
        };
        $this->logger->info('event_name', [])->shouldBeCalled();
        $this->eventSubscriber->handleMessage($event);
    }
}
