<?php

declare(strict_types=1);

namespace App\Tests\Messenger\EventListener;

use App\Event\NamedEventInterface;
use App\Messenger\EventListener\MessengerEventsListener;
use Clogger\ContextualInterface;
use PHPUnit\Framework\TestCase;
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
class MessengerEventsListenerTest extends TestCase
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
    public function testOnMessageHandled(object $message, array $context)
    {
        $event = new WorkerMessageHandledEvent(new Envelope($message, []), '');
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
    public function testOnMessageReceived(object $message, array $context)
    {
        $event = new WorkerMessageReceivedEvent(new Envelope($message, []), '');
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
    public function testOnMessageSent(object $message, array $context)
    {
        $event = new SendMessageToTransportsEvent(new Envelope($message, []));
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
    public function testOnMessageFailed(object $message, array $context)
    {
        $exc = new \Exception();
        $event = new WorkerMessageFailedEvent(new Envelope($message, []), '', $exc);
        $this->logger->error($exc, $context)->shouldBeCalled();
        $this->logger->info('Error while handling message', $context + ['exception' => $exc])->shouldBeCalled();

        $this->messengerEventsListener->onMessageFailed($event);
    }

    public function dataProvider(): iterable
    {
        $message = new \stdClass();

        yield [
            $message,
            ['message' => 'stdClass', 'event_name' => null, 'message_parsed' => $message],
        ];

        $message = new class() implements ContextualInterface {
            public function getContext(): array
            {
                return ['a' => 'b'];
            }
        };

        yield [
            $message,
            ['message' => get_class($message), 'event_name' => null, 'message_parsed' => ['a' => 'b']],
        ];

        $message = new class() implements NamedEventInterface {
            public function getEventName(): string
            {
                return 'named event';
            }
        };

        yield [
            $message,
            ['message' => get_class($message), 'event_name' => 'named event', 'message_parsed' => $message],
        ];
    }
}
