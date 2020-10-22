<?php

declare(strict_types=1);

namespace App\Tests\Messenger\EventListener;

use App\Helper\EaiTransactionId;
use App\Http\CorrelationId\CorrelationId;
use App\Messenger\EventListener\MessengerHeaderLoggingListener;
use App\Messenger\Stamp\CorrelationIdStamp;
use App\Messenger\Stamp\EaiTransactionIdStamp;
use App\Tests\ProphecyTestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use Symfony\Component\Messenger\Event\WorkerMessageHandledEvent;
use Symfony\Component\Messenger\Event\WorkerMessageReceivedEvent;

/**
 * @coversDefaultClass \App\Messenger\EventListener\MessengerHeaderLoggingListener
 */
class MessengerHeaderLoggingListenerTest extends ProphecyTestCase
{
    /**
     * @var EaiTransactionId|ObjectProphecy
     */
    private $eaiTransactionId;

    /**
     * @var CorrelationId|ObjectProphecy
     */
    private $correlationId;

    private MessengerHeaderLoggingListener $messengerHeaderLoggingListener;

    public function setUp(): void
    {
        $this->eaiTransactionId = $this->prophesize(EaiTransactionId::class);
        $this->correlationId = $this->prophesize(CorrelationId::class);
        $this->messengerHeaderLoggingListener = new MessengerHeaderLoggingListener(
            $this->eaiTransactionId->reveal(),
            $this->correlationId->reveal()
        );
    }

    /**
     * @covers ::getSubscribedEvents
     */
    public function testGetSubscribedEvents(): void
    {
        $this->assertEquals([
            WorkerMessageFailedEvent::class => ['onMessageFailed', 100],
            WorkerMessageHandledEvent::class => ['onMessageHandled', 100],
            WorkerMessageReceivedEvent::class => ['onMessageReceived', 100],
        ], MessengerHeaderLoggingListener::getSubscribedEvents());
    }

    /**
     * @covers ::onMessageFailed
     * @covers ::resetOverrides
     * @covers ::__construct
     */
    public function testOnMessageFailed(): void
    {
        $event = new WorkerMessageFailedEvent(new Envelope(new \stdClass()), 'aaa', new \Exception());
        $this->resetOverrides();
        $this->messengerHeaderLoggingListener->onMessageFailed($event);
    }

    /**
     * @covers ::onMessageHandled
     * @covers ::resetOverrides
     * @covers ::__construct
     */
    public function testOnMessageHandled(): void
    {
        $event = new WorkerMessageHandledEvent(new Envelope(new \stdClass()), 'aaa');
        $this->resetOverrides();
        $this->messengerHeaderLoggingListener->onMessageHandled($event);
    }

    private function resetOverrides(): void
    {
        $this->eaiTransactionId->resetTransactionIdOverride()->shouldBeCalled();
        $this->correlationId->resetCorrelationIdOverride()->shouldBeCalled();
        $this->correlationId->regenerate()->shouldBeCalled();
    }

    /**
     * @covers ::onMessageReceived
     * @covers ::__construct
     */
    public function testOnMessageReceivedWithNoStamps(): void
    {
        $envelope = new Envelope(new \stdClass(), []);
        $event = new WorkerMessageReceivedEvent($envelope, 'aaa');
        $this->eaiTransactionId->setTransactionIdOverride(Argument::any())->shouldNotBeCalled();
        $this->correlationId->setCorrelationIdOverride(Argument::any())->shouldNotBeCalled();
        $this->messengerHeaderLoggingListener->onMessageReceived($event);
    }

    /**
     * @covers ::onMessageReceived
     * @covers ::__construct
     */
    public function testOnMessageReceivedWithEaiTransactionIdStamp(): void
    {
        $eaiTransactionId = '1234';
        $envelope = new Envelope(new \stdClass(), [new EaiTransactionIdStamp($eaiTransactionId)]);
        $event = new WorkerMessageReceivedEvent($envelope, 'aaa');
        $this->eaiTransactionId->setTransactionIdOverride($eaiTransactionId)->shouldBeCalled();
        $this->correlationId->setCorrelationIdOverride(Argument::any())->shouldNotBeCalled();
        $this->messengerHeaderLoggingListener->onMessageReceived($event);
    }

    /**
     * @covers ::onMessageReceived
     * @covers ::__construct
     */
    public function testOnMessageReceivedWithCorrelationIdStamp(): void
    {
        $correlationId = '6789';
        $envelope = new Envelope(new \stdClass(), [new CorrelationIdStamp($correlationId)]);
        $event = new WorkerMessageReceivedEvent($envelope, 'aaa');
        $this->eaiTransactionId->setTransactionIdOverride(Argument::any())->shouldNotBeCalled();
        $this->correlationId->setCorrelationIdOverride($correlationId)->shouldBeCalled();
        $this->messengerHeaderLoggingListener->onMessageReceived($event);
    }

    /**
     * @covers ::onMessageReceived
     * @covers ::__construct
     */
    public function testOnMessageReceivedWithBothEaiTransactionIdAndCorrelationIdStamps(): void
    {
        $eaiTransactionId = '1234';
        $correlationId = '6789';
        $envelope = new Envelope(
            new \stdClass(),
            [
                new EaiTransactionIdStamp($eaiTransactionId),
                new CorrelationIdStamp($correlationId),
            ]
        );
        $event = new WorkerMessageReceivedEvent($envelope, 'aaa');
        $this->eaiTransactionId->setTransactionIdOverride($eaiTransactionId)->shouldBeCalled();
        $this->correlationId->setCorrelationIdOverride($correlationId)->shouldBeCalled();
        $this->messengerHeaderLoggingListener->onMessageReceived($event);
    }
}
