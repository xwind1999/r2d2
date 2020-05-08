<?php

declare(strict_types=1);

namespace App\Tests\EventSubscriber;

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
        $this->assertIsArray(LoggableEventSubscriber::getSubscribedEvents());
    }

    public function testLogEvent()
    {
        $event = $this->prophesize(LoggableEventInterface::class);
        $event->getLevel()->willReturn('notice');
        $this->logger->log('notice', $event)->shouldBeCalled();
        $this->subscriber->logEvent($event->reveal());
    }
}
