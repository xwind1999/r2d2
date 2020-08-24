<?php

declare(strict_types=1);

namespace App\Tests\EventSubscriber;

use App\Event\NamedEventInterface;
use App\EventSubscriber\NamedEventSubscriber;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\EventDispatcher\Event;

class NamedEventSubscriberTest extends TestCase
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
            [Event::class => ['handleMessage']],
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
        };
        $this->logger->info('event_name')->shouldBeCalled();
        $this->eventSubscriber->handleMessage($event);
    }

    public function testHandleMessageWithoutNamedEvent()
    {
        $event = new Event();
        $this->logger->info(Argument::any())->shouldNotBeCalled();
        $this->eventSubscriber->handleMessage($event);
    }
}
