<?php

declare(strict_types=1);

namespace App\Tests\Messenger;

use App\Contract\Request\BroadcastListener\Product\Product;
use App\Contract\Request\BroadcastListener\RoomAvailabilityRequest;
use App\Exception\Repository\ComponentNotFoundException;
use App\Exception\Repository\ExperienceNotFoundException;
use App\Exception\Repository\RoomAvailabilityNotFoundException;
use App\Exception\Resolver\UnprocessableProductTypeException;
use App\Messenger\RetryCustomStrategy;
use App\Tests\ProphecyTestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpReceivedStamp;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\Exception\RejectRedeliveredMessageException;
use Symfony\Component\Messenger\Stamp\BusNameStamp;
use Symfony\Component\Messenger\Stamp\RedeliveryStamp;

/**
 * @coversDefaultClass \App\Messenger\RetryCustomStrategy
 * @group retry-strategy
 */
class RetryCustomStrategyTest extends ProphecyTestCase
{
    private RetryCustomStrategy $retryCustomStrategy;
    private ObjectProphecy $logger;

    public function setUp(): void
    {
        $this->logger = $this->prophesize(LoggerInterface::class);
        $this->retryCustomStrategy = new RetryCustomStrategy($this->logger->reveal());
    }

    /**
     * @dataProvider retryableProvider
     * @cover ::isRetryable
     */
    public function testIsRetryable(Envelope $message, $throwable, callable $asserts): void
    {
        $isRetryable = $this->retryCustomStrategy->isRetryable($message, $throwable);

        $asserts($this, $isRetryable);
    }

    public function retryableProvider(): ?\Generator
    {
        $busNameStamp = new BusNameStamp('retry-test');
        $amqpReceiveStamp = $this->prophesize(AmqpReceivedStamp::class);
        $stamps = [$busNameStamp, $amqpReceiveStamp->reveal()];

        $roomAvailabilityRequest = new RoomAvailabilityRequest();
        $product = new Product();
        $product->id = '481d7e979637c39f6864d709';
        $roomAvailabilityRequest->product = $product;
        $roomAvailabilityRequest->quantity = 0;
        $roomAvailabilityRequest->dateFrom = new \DateTime('now');
        $roomAvailabilityRequest->dateTo = (clone $roomAvailabilityRequest->dateFrom)->modify('3 days');
        $roomAvailabilityRequest->updatedAt = new \DateTIme('now');
        $roomAvailabilityRequest->isStopSale = false;

        $message = new Envelope($roomAvailabilityRequest, $stamps);

        $logger = $this->prophesize(LoggerInterface::class);

        yield 'first-try' => [
            $message,
            (function ($message, $logger) {
                $exceptions = [new ComponentNotFoundException()];
                $throwable = new HandlerFailedException($message, $exceptions);
                $logger->error()->shouldNotBeCalled();

                return $throwable;
            })($message, $logger),
            (static function ($test, $isRetryable) {
                $test->assertEquals(true, $isRetryable);
            }),
        ];

        yield 'first-retry' => [
            (static function ($message, $stamps) {
                $stamps[] = new RedeliveryStamp(1);
                $message = new Envelope($message, $stamps);

                return $message;
            })(clone $message, $stamps),
            (function ($message) {
                $exceptions = [new ExperienceNotFoundException()];

                return new HandlerFailedException($message, $exceptions);
            })($message),
            (static function ($test, $isRetryable) {
                $test->assertEquals(true, $isRetryable);
            }),
        ];

        yield 'unprocessable-exception' => [
            (static function ($message, $stamps) {
                $stamps[] = new RedeliveryStamp(1);
                $message = new Envelope($message, $stamps);

                return $message;
            })(clone $message, $stamps),
            (function ($message, $logger) {
                $exceptions = [new UnprocessableProductTypeException()];
                $throwable = new HandlerFailedException($message, $exceptions);
                $logger->error()->shouldBeCalledOnce();

                return $throwable;
            })($message, $logger),
            (static function ($test, $isRetryable) {
                $test->assertEquals(false, $isRetryable);
            }),
        ];

        yield '200th-retry' => [
            (static function ($message, $stamps) {
                $stamps[] = new RedeliveryStamp(200);
                $message = new Envelope($message, $stamps);

                return $message;
            })(clone $message, $stamps),
            (function ($message) {
                $exceptions = [new RoomAvailabilityNotFoundException()];

                return new HandlerFailedException($message, $exceptions);
            })($message),
            (static function ($test, $isRetryable) {
                $test->assertEquals(false, $isRetryable);
            }),
        ];
    }

    /**
     * @dataProvider messageProvider
     * @cover ::getWaitingTime
     */
    public function testGetWaitingTime(Envelope $message, \Throwable $throwable, callable $asserts): void
    {
        $waitingTime = $this->retryCustomStrategy->getWaitingTime($message, $throwable);

        $asserts($this, $waitingTime);
    }

    public function messageProvider(): ?\Generator
    {
        $busNameStamp = new BusNameStamp('retry-test');
        $amqpReceiveStamp = $this->prophesize(AmqpReceivedStamp::class);
        $stamps = [$busNameStamp, $amqpReceiveStamp->reveal()];

        $roomAvailabilityRequest = new RoomAvailabilityRequest();
        $product = new Product();
        $product->id = '481d7e979637c39f6864d709';
        $roomAvailabilityRequest->product = $product;
        $roomAvailabilityRequest->quantity = 0;
        $roomAvailabilityRequest->dateFrom = new \DateTime('now');
        $roomAvailabilityRequest->dateTo = (clone $roomAvailabilityRequest->dateFrom)->modify('3 days');
        $roomAvailabilityRequest->updatedAt = new \DateTIme('now');
        $roomAvailabilityRequest->isStopSale = false;

        $message = new Envelope($roomAvailabilityRequest, $stamps);

        $throwable = $this->prophesize(RejectRedeliveredMessageException::class)->reveal();

        yield 'first-try' => [
            $message,
            $throwable,
            static function ($test, $waitingTime) {
                $test->assertEquals(10000, $waitingTime);
            },
        ];

        yield 'first-time-failure' => [
            (static function ($message, $stamps) {
                $stamps[] = new RedeliveryStamp(1);
                $message = new Envelope($message, $stamps);

                return $message;
            })(clone $message, $stamps),
            $throwable,
            static function ($test, $waitingTime) {
                $test->assertEquals(10000, $waitingTime);
            },
        ];

        yield 'second-time-failure' => [
            (static function ($message, $stamps) {
                $stamps[] = new RedeliveryStamp(2);
                $message = new Envelope($message, $stamps);

                return $message;
            })(clone $message, $stamps),
            $throwable,
            function ($test, $waitingTime) {
                $test->assertEquals(30000, $waitingTime);
            },
        ];

        yield 'third-time-failure' => [
            (static function ($message, $stamps) {
                $stamps[] = new RedeliveryStamp(3);
                $message = new Envelope($message, $stamps);

                return $message;
            })(clone $message, $stamps),
            $throwable,
            static function ($test, $waitingTime) {
                $test->assertEquals(60000, $waitingTime);
            },
        ];

        yield 'fourth-time-failure' => [
            (static function ($message, $stamps) {
                $stamps[] = new RedeliveryStamp(4);
                $message = new Envelope($message, $stamps);

                return $message;
            })(clone $message, $stamps),
            $throwable,
            static function ($test, $waitingTime) {
                $test->assertEquals(60000, $waitingTime);
            },
        ];

        yield 'fifth-time-failure' => [
            (static function ($message, $stamps) {
                $stamps[] = new RedeliveryStamp(5);
                $message = new Envelope($message, $stamps);

                return $message;
            })(clone $message, $stamps),
            $throwable,
            static function ($test, $waitingTime) {
                $test->assertEquals(60000, $waitingTime);
            },
        ];

        yield 'sixth-time-failure' => [
            (static function ($message, $stamps) {
                $stamps[] = new RedeliveryStamp(6);
                $message = new Envelope($message, $stamps);

                return $message;
            })(clone $message, $stamps),
            $throwable,
            static function ($test, $waitingTime) {
                $test->assertEquals(60000, $waitingTime);
            },
        ];

        yield 'seventh-time-failure' => [
            (static function ($message, $stamps) {
                $stamps[] = new RedeliveryStamp(7);
                $message = new Envelope($message, $stamps);

                return $message;
            })(clone $message, $stamps),
            $throwable,
            static function ($test, $waitingTime) {
                $test->assertEquals(600000, $waitingTime);
            },
        ];

        yield 'eighth-time-failure' => [
            (static function ($message, $stamps) {
                $stamps[] = new RedeliveryStamp(8);
                $message = new Envelope($message, $stamps);

                return $message;
            })(clone $message, $stamps),
            $throwable,
            static function ($test, $waitingTime) {
                $test->assertEquals(600000, $waitingTime);
            },
        ];

        yield 'ninth-time-failure' => [
            (static function ($message, $stamps) {
                $stamps[] = new RedeliveryStamp(9);
                $message = new Envelope($message, $stamps);

                return $message;
            })(clone $message, $stamps),
            $throwable,
            static function ($test, $waitingTime) {
                $test->assertEquals(600000, $waitingTime);
            },
        ];

        yield 'tenth-time-failure' => [
            (static function ($message, $stamps) {
                $stamps[] = new RedeliveryStamp(10);
                $message = new Envelope($message, $stamps);

                return $message;
            })(clone $message, $stamps),
            $throwable,
            static function ($test, $waitingTime) {
                $test->assertEquals(600000, $waitingTime);
            },
        ];

        yield 'eleventh-time-failure' => [
            (static function ($message, $stamps) {
                $stamps[] = new RedeliveryStamp(11);
                $message = new Envelope($message, $stamps);

                return $message;
            })(clone $message, $stamps),
            $throwable,
            static function ($test, $waitingTime) {
                $test->assertEquals(1800000, $waitingTime);
            },
        ];
    }
}
