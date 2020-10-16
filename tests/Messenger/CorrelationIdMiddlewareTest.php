<?php

declare(strict_types=1);

namespace App\Tests\Messenger;

use App\Http\CorrelationId\CorrelationId;
use App\Messenger\CorrelationIdMiddleware;
use App\Messenger\Stamp\CorrelationIdStamp;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\SentStamp;

/**
 * @coversDefaultClass \App\Messenger\CorrelationIdMiddleware
 */
class CorrelationIdMiddlewareTest extends TestCase
{
    private $correlationId;

    private $stack;

    private $middleware;

    private CorrelationIdMiddleware $correlationIdMiddleware;

    public function setUp(): void
    {
        $this->correlationId = $this->prophesize(CorrelationId::class);
        $this->stack = $this->prophesize(StackInterface::class);
        $this->middleware = $this->prophesize(MiddlewareInterface::class);
        $this->correlationIdMiddleware = new CorrelationIdMiddleware($this->correlationId->reveal());
    }

    /**
     * @covers ::__construct
     * @covers ::handle
     */
    public function testHandle(): void
    {
        $envelope = new Envelope(new \stdClass(), [new SentStamp('whatever')]);
        $this->correlationId->getUuid()->willReturn('1234');
        (function ($test, $envelope) {
            $this
                ->middleware
                ->handle(Argument::type(Envelope::class), $this->stack->reveal())
                ->shouldBeCalled()
                ->will(function ($args) use ($test, $envelope) {
                    $stamp = $args[0]->last(CorrelationIdStamp::class);
                    $test->assertEquals('1234', $stamp->correlationId);

                    return $envelope;
                });
        })($this, $envelope);

        $this->stack->next()->willReturn($this->middleware->reveal());
        $this->correlationIdMiddleware->handle($envelope, $this->stack->reveal());
    }

    /**
     * @covers ::__construct
     * @covers ::handle
     */
    public function testHandleWithExistingCorrelationIdStamp(): void
    {
        $envelope = new Envelope(new \stdClass(), [
            new SentStamp('whatever'),
            new CorrelationIdStamp('5678'),
        ]);
        (function ($test, $envelope) {
            $this
                ->middleware
                ->handle(Argument::type(Envelope::class), $this->stack->reveal())
                ->shouldBeCalled()
                ->will(function ($args) use ($test, $envelope) {
                    $stamp = $args[0]->last(CorrelationIdStamp::class);
                    $test->assertEquals('5678', $stamp->correlationId);

                    return $envelope;
                });
        })($this, $envelope);

        $this->stack->next()->willReturn($this->middleware->reveal());
        $this->correlationIdMiddleware->handle($envelope, $this->stack->reveal());
    }

    /**
     * @covers ::__construct
     * @covers ::handle
     */
    public function testHandleWithUnsentMessage(): void
    {
        $envelope = new Envelope(new \stdClass());
        $this->correlationId->getUuid()->willReturn('1234');
        (function ($test, $envelope) {
            $this
                ->middleware
                ->handle(Argument::type(Envelope::class), $this->stack->reveal())
                ->shouldBeCalled()
                ->will(function ($args) use ($test, $envelope) {
                    $test->assertEquals($envelope, $args[0]);

                    return $envelope;
                });
        })($this, $envelope);

        $this->stack->next()->willReturn($this->middleware->reveal());
        $this->correlationIdMiddleware->handle($envelope, $this->stack->reveal());
    }
}
