<?php

declare(strict_types=1);

namespace App\Tests\Messenger\EventListener;

use App\Event\NamedEventInterface;
use App\Messenger\EventListener\MessengerEventsListener;
use App\Messenger\Stamp\CorrelationIdStamp;
use App\Messenger\Stamp\EaiTransactionIdStamp;
use App\Tests\ProphecyTestCase;
use Clogger\ContextualInterface;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Event\SendMessageToTransportsEvent;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use Symfony\Component\Messenger\Event\WorkerMessageHandledEvent;
use Symfony\Component\Messenger\Event\WorkerMessageReceivedEvent;

/**
 * @coversDefaultClass \App\Messenger\EventListener\MessengerEventsListener
 */
class MessengerEventsListenerTest extends ProphecyTestCase
{
    /**
     * @var LoggerInterface|ObjectProphecy
     */
    private $logger;

    private MessengerEventsListener $messengerEventsListener;

    public function setUp(): void
    {
        $this->logger = $this->prophesize(LoggerInterface::class);
        $this->messengerEventsListener = new MessengerEventsListener($this->logger->reveal());
    }

    /**
     * @covers ::__construct
     * @covers ::onMessageHandled
     * @covers ::generateContext
     *
     * @dataProvider dataProvider
     */
    public function testOnMessageHandled(object $message, array $stamps, array $context)
    {
        $event = new WorkerMessageHandledEvent(new Envelope($message, $stamps), '');
        $this->logger->info('Message handled', $context)->shouldBeCalled();
        $this->messengerEventsListener->onMessageHandled($event);
    }

    /**
     * @covers ::__construct
     * @covers ::onMessageReceived
     * @covers ::generateContext
     *
     * @dataProvider dataProvider
     */
    public function testOnMessageReceived(object $message, array $stamps, array $context)
    {
        $event = new WorkerMessageReceivedEvent(new Envelope($message, $stamps), '');
        $this->logger->info('Message received', $context)->shouldBeCalled();
        $this->messengerEventsListener->onMessageReceived($event);
    }

    /**
     * @covers ::__construct
     * @covers ::onMessageSent
     * @covers ::generateContext
     *
     * @dataProvider dataProvider
     */
    public function testOnMessageSent(object $message, array $stamps, array $context)
    {
        $event = new SendMessageToTransportsEvent(new Envelope($message, $stamps));
        $this->logger->info('Message sent', $context)->shouldBeCalled();
        $this->messengerEventsListener->onMessageSent($event);
    }

    /**
     * @covers ::getSubscribedEvents
     */
    public function testGetSubscribedEvents()
    {
        $this->assertEquals([
            WorkerMessageFailedEvent::class => ['onMessageFailed', 100],
            SendMessageToTransportsEvent::class => ['onMessageSent', 100],
            WorkerMessageHandledEvent::class => ['onMessageHandled', 100],
            WorkerMessageReceivedEvent::class => ['onMessageReceived', 100],
        ], MessengerEventsListener::getSubscribedEvents());
    }

    /**
     * @covers ::__construct
     * @covers ::onMessageFailed
     * @covers ::generateContext
     *
     * @dataProvider dataProvider
     */
    public function testOnMessageFailed(object $message, array $stamps, array $context)
    {
        $exc = new \Exception();
        $event = new WorkerMessageFailedEvent(new Envelope($message, $stamps), '', $exc);
        $this->logger->error($exc, $context)->shouldBeCalled();
        $this->logger->info('Error while handling message', $context + ['exception' => $exc])->shouldBeCalled();

        $this->messengerEventsListener->onMessageFailed($event);
    }

    public function dataProvider(): iterable
    {
        $message = new \stdClass();

        yield 'generic message' => [
            $message,
            [],
            ['message' => 'stdClass', 'event_name' => null, 'message_parsed' => $message],
        ];

        yield 'generic message with eai transaction id stamp' => [
            $message,
            [new EaiTransactionIdStamp('1234')],
            ['eai_transaction_id' => '1234', 'message' => 'stdClass', 'event_name' => null, 'message_parsed' => $message],
        ];

        yield 'generic message with correlation id stamp' => [
            $message,
            [new CorrelationIdStamp('5678')],
            ['correlation_id' => '5678', 'message' => 'stdClass', 'event_name' => null, 'message_parsed' => $message],
        ];

        $message = new class() implements ContextualInterface {
            public function getContext(): array
            {
                return ['a' => 'b'];
            }
        };

        yield 'generic message with context' => [
            $message,
            [],
            ['message' => get_class($message), 'event_name' => null, 'message_parsed' => ['a' => 'b']],
        ];

        $message = new class() implements NamedEventInterface {
            public function getEventName(): string
            {
                return 'named event';
            }

            public function getContext(): array
            {
                return ['z' => 'd'];
            }
        };

        yield 'NamedEvent message with context' => [
            $message,
            [],
            ['message' => get_class($message), 'event_name' => 'named event', 'message_parsed' => ['z' => 'd']],
        ];
    }
}
