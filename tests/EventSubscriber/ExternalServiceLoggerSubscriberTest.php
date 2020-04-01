<?php

declare(strict_types=1);

namespace App\Tests\EventSubscriber;

use App\Event\Http\ExternalServiceRequestMadeEvent;
use App\Event\Http\MalformedResponseReceivedEvent;
use App\Event\Http\WellFormedResponseReceivedEvent;
use App\EventSubscriber\ExternalServiceLoggerSubscriber;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;

class ExternalServiceLoggerSubscriberTest extends TestCase
{
    protected ExternalServiceLoggerSubscriber $subscriber;

    /**
     * @var LoggerInterface|ObjectProphecy
     */
    protected $logger;

    public function setUp(): void
    {
        $this->logger = $this->prophesize(LoggerInterface::class);
        $this->subscriber = new ExternalServiceLoggerSubscriber($this->logger->reveal());
    }

    public function testGetSubscribedEvents()
    {
        $this->assertIsArray(ExternalServiceLoggerSubscriber::getSubscribedEvents());
    }

    public function testLogWellFormedResponseReceived()
    {
        $event = $this->prophesize(WellFormedResponseReceivedEvent::class)->reveal();
        $this->logger->notice($event)->shouldBeCalled();
        $this->subscriber->logWellFormedResponseReceived($event);
    }

    public function testLogMalformedResponseReceived()
    {
        $event = $this->prophesize(MalformedResponseReceivedEvent::class)->reveal();
        $this->logger->error($event)->shouldBeCalled();
        $this->subscriber->logMalformedResponseReceived($event);
    }

    public function testLogRequestMade()
    {
        $event = $this->prophesize(ExternalServiceRequestMadeEvent::class)->reveal();
        $this->logger->notice($event)->shouldBeCalled();
        $this->subscriber->logRequestMade($event);
    }
}
