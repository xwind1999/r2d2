<?php

declare(strict_types=1);

namespace App\Tests\Messenger;

use App\Messenger\NewrelicMessengerMiddleware;
use Ekino\NewRelicBundle\NewRelic\NewRelicInteractorInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\ReceivedStamp;

class NewrelicMessengerMiddlewareTest extends TestCase
{
    /**
     * @var NewRelicInteractorInterface|ObjectProphecy
     */
    private $newrelicInteractor;

    private NewrelicMessengerMiddleware $newrelicMessengerMiddleware;

    public function setUp(): void
    {
        $this->newrelicInteractor = $this->prophesize(NewRelicInteractorInterface::class);
        $this->newrelicMessengerMiddleware = new NewrelicMessengerMiddleware($this->newrelicInteractor->reveal());
    }

    public function testHandleForSentMessages()
    {
        $envelope = new Envelope(new \stdClass());
        $stack = $this->prophesize(StackInterface::class);
        $next = $this->prophesize(MiddlewareInterface::class);
        $next->handle($envelope, $stack->reveal())->willReturn($envelope)->shouldBeCalled();
        $stack->next()->shouldBeCalled()->willReturn($next->reveal());

        $this->newrelicMessengerMiddleware->handle($envelope, $stack->reveal());
    }

    public function testHandleForReceivedMessages()
    {
        $envelope = new Envelope(new \stdClass(), [new ReceivedStamp('z')]);
        $stack = $this->prophesize(StackInterface::class);
        $next = $this->prophesize(MiddlewareInterface::class);
        $next->handle($envelope, $stack->reveal())->willReturn($envelope)->shouldBeCalled();
        $stack->next()->shouldBeCalled()->willReturn($next->reveal());

        $this->newrelicInteractor->startTransaction()->shouldBeCalled();
        $this->newrelicInteractor->endTransaction()->shouldBeCalled();
        $this->newrelicInteractor->setTransactionName('stdClass')->shouldBeCalled();
        $this->newrelicMessengerMiddleware->handle($envelope, $stack->reveal());
    }

    public function testHandleForReceivedMessagesWillThrowException()
    {
        $exception = $this->prophesize(\Exception::class)->reveal();
        $envelope = new Envelope(new \stdClass(), [new ReceivedStamp('z')]);
        $stack = $this->prophesize(StackInterface::class);
        $next = $this->prophesize(MiddlewareInterface::class);
        $next->handle($envelope, $stack->reveal())->willThrow($exception);
        $stack->next()->shouldBeCalled()->willReturn($next->reveal());

        $this->newrelicInteractor->startTransaction()->shouldBeCalled();
        $this->newrelicInteractor->endTransaction()->shouldBeCalled();
        $this->newrelicInteractor->setTransactionName('stdClass')->shouldBeCalled();

        $this->expectException(\Exception::class);
        $this->newrelicMessengerMiddleware->handle($envelope, $stack->reveal());
    }
}
