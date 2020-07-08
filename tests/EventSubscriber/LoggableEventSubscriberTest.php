<?php

declare(strict_types=1);

namespace App\Tests\EventSubscriber;

use App\Event\Http\BadResponseReceivedEvent;
use App\Event\Http\ExternalServiceRequestMadeEvent;
use App\Event\Http\WellFormedResponseReceivedEvent;
use App\EventSubscriber\LoggableEventSubscriber;
use App\Helper\LoggableEventInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;

class LoggableEventSubscriberTest extends TestCase
{
    protected LoggableEventSubscriber $subscriber;

    /**
     * @var LoggerInterface|ObjectProphecy
     */
    protected $logger;

    public function setUp(): void
    {
        $this->logger = $this->prophesize(LoggerInterface::class);
        $this->subscriber = new LoggableEventSubscriber($this->logger->reveal());
    }

    public function testGetSubscribedEvents()
    {
        $this->assertEquals(
            [
                LoggableEventInterface::class => ['logEvent', 100],
                BadResponseReceivedEvent::class => ['logEvent', 100],
                ExternalServiceRequestMadeEvent::class => ['logEvent', 100],
                WellFormedResponseReceivedEvent::class => ['logEvent', 100],
            ],
            LoggableEventSubscriber::getSubscribedEvents());
    }

    public function testLogEvent()
    {
        $event = $this->prophesize(LoggableEventInterface::class);
        $event->getLevel()->willReturn('notice');
        $this->logger->log('notice', $event)->shouldBeCalled();
        $this->subscriber->logEvent($event->reveal());
    }
}
