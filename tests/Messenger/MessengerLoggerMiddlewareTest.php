<?php

declare(strict_types=1);

namespace App\Tests\Messenger;

use App\Event\NamedEventInterface;
use App\Messenger\MessengerLoggerMiddleware;
use Clogger\ContextualInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\ReceivedStamp;

class MessengerLoggerMiddlewareTest extends TestCase
{
    /**
     * @var LoggerInterface|ObjectProphecy
     */
    private $logger;

    private MessengerLoggerMiddleware $messengerLoggerMiddleware;

    public function setUp(): void
    {
        $this->logger = $this->prophesize(LoggerInterface::class);
        $this->messengerLoggerMiddleware = new MessengerLoggerMiddleware($this->logger->reveal());
    }

    /**
     * @dataProvider dataProvider
     */
    public function testHandleForReceivedMessages(object $message, array $stamps, callable $asserts)
    {
        $envelope = new Envelope($message, $stamps);
        $stack = $this->prophesize(StackInterface::class);
        $next = $this->prophesize(\Symfony\Component\Messenger\Middleware\MiddlewareInterface::class);
        $next->handle($envelope, $stack->reveal())->willReturn($envelope)->shouldBeCalled();
        $stack->next()->shouldBeCalled()->willReturn($next->reveal());
        $this->messengerLoggerMiddleware->handle($envelope, $stack->reveal());
        $asserts($this);
    }

    public function dataProvider(): iterable
    {
        $message = new \stdClass();
        yield [
            $message,
            [new ReceivedStamp('z')],
            function ($c) use ($message) {
                $c->logger->info()->shouldNotHaveBeenCalled();
            },
        ];

        yield [
            $message,
            [],
            function ($c) use ($message) {
                $c->logger->info('Message received', ['message' => 'stdClass', 'event_name' => null, 'message_parsed' => $message])->shouldHaveBeenCalled();
            },
        ];

        $message = new class() implements ContextualInterface {
            public function getContext(): array
            {
                return ['a' => 'b'];
            }
        };
        yield [
            $message,
            [],
            function ($c) use ($message) {
                $c->logger->info('Message received', ['message' => get_class($message), 'event_name' => null, 'message_parsed' => ['a' => 'b']])->shouldHaveBeenCalled();
            },
        ];

        $message = new class() implements NamedEventInterface {
            public function getEventName(): string
            {
                return 'named event';
            }
        };
        yield [
            $message,
            [],
            function ($c) use ($message) {
                $c->logger->info('Message received', ['message' => get_class($message), 'event_name' => 'named event', 'message_parsed' => $message])->shouldHaveBeenCalled();
            },
        ];
    }
}
