<?php

declare(strict_types=1);

namespace App\Tests\Messenger;

use App\Helper\EaiTransactionId;
use App\Messenger\EaiTransactionIdMiddleware;
use App\Messenger\Stamp\EaiTransactionIdStamp;
use App\Tests\ProphecyTestCase;
use Prophecy\Argument;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\SentStamp;

/**
 * @coversDefaultClass \App\Messenger\EaiTransactionIdMiddleware
 */
class EaiTransactionIdMiddlewareTest extends ProphecyTestCase
{
    private $eaiTransactionId;

    private $stack;

    private $middleware;

    private EaiTransactionIdMiddleware $eaiTransactionIdMiddleware;

    public function setUp(): void
    {
        $this->eaiTransactionId = $this->prophesize(EaiTransactionId::class);
        $this->stack = $this->prophesize(StackInterface::class);
        $this->middleware = $this->prophesize(MiddlewareInterface::class);
        $this->eaiTransactionIdMiddleware = new EaiTransactionIdMiddleware($this->eaiTransactionId->reveal());
    }

    /**
     * @covers ::__construct
     * @covers ::handle
     */
    public function testHandle(): void
    {
        $envelope = new Envelope(new \stdClass(), [new SentStamp('whatever')]);
        $this->eaiTransactionId->getTransactionId()->willReturn('1234');
        (function ($test, $envelope) {
            $this
                ->middleware
                ->handle(Argument::type(Envelope::class), $this->stack->reveal())
                ->shouldBeCalled()
                ->will(function ($args) use ($test, $envelope) {
                    $stamp = $args[0]->last(EaiTransactionIdStamp::class);
                    $test->assertEquals('1234', $stamp->eaiTransactionId);

                    return $envelope;
                });
        })($this, $envelope);

        $this->stack->next()->willReturn($this->middleware->reveal());
        $this->eaiTransactionIdMiddleware->handle($envelope, $this->stack->reveal());
    }

    /**
     * @covers ::__construct
     * @covers ::handle
     */
    public function testHandleWithExistingEaiTransactionIdStamp(): void
    {
        $envelope = new Envelope(new \stdClass(), [
            new SentStamp('whatever'),
            new EaiTransactionIdStamp('5678'),
        ]);
        (function ($test, $envelope) {
            $this
                ->middleware
                ->handle(Argument::type(Envelope::class), $this->stack->reveal())
                ->shouldBeCalled()
                ->will(function ($args) use ($test, $envelope) {
                    $stamp = $args[0]->last(EaiTransactionIdStamp::class);
                    $test->assertEquals('5678', $stamp->eaiTransactionId);

                    return $envelope;
                });
        })($this, $envelope);

        $this->eaiTransactionId->getTransactionId()->willReturn('whatever');
        $this->stack->next()->willReturn($this->middleware->reveal());
        $this->eaiTransactionIdMiddleware->handle($envelope, $this->stack->reveal());
    }
}
