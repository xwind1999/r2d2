<?php

declare(strict_types=1);

namespace App\Tests\EventSubscriber;

use App\EventSubscriber\ApiExceptionSubscriber;
use App\Exception\Http\ApiException;
use App\Kernel;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy as ObjectProphecyAlias;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * @coversDefaultClass \App\EventSubscriber\ApiExceptionSubscriber
 */
class ApiExceptionSubscriberTest extends TestCase
{
    /**
     * @var LoggerInterface|ObjectProphecyAlias
     */
    protected $logger;

    public function setUp(): void
    {
        $this->logger = $this->prophesize(LoggerInterface::class);
    }

    /**
     * @covers ::getSubscribedEvents
     */
    public function testGetSubscribedEvents(): void
    {
        $this->assertEquals([KernelEvents::EXCEPTION => 'onKernelException'], ApiExceptionSubscriber::getSubscribedEvents());
    }

    /**
     * @dataProvider providerOnKernelException
     *
     * @covers ::__construct
     * @covers ::onKernelException
     */
    public function testOnKernelException(\Exception $exception, string $message, int $code, int $httpCode): void
    {
        $event = new ExceptionEvent(
            new Kernel('prod', false),
            new Request(),
            1,
            $exception
        );

        $this->logger->error(Argument::type(\Exception::class))->shouldBeCalled();
        $response = new JsonResponse(['error' => ['message' => $message, 'code' => $code]], $httpCode);

        $apiExceptionSubscriber = new ApiExceptionSubscriber($this->logger->reveal());
        $apiExceptionSubscriber->onKernelException($event);
        $this->assertEquals($response, $event->getResponse());
    }

    /**
     * @covers ::__construct
     * @covers ::onKernelException
     */
    public function testOnKernelExceptionForNonProduction(): void
    {
        $event = new ExceptionEvent(
            new Kernel('dev', false),
            new Request([], [], [], [], [], ['APP_ENV' => 'dev']),
            1,
            new \Exception()
        );

        $this->logger->error(Argument::type(\Exception::class))->shouldBeCalled();

        $apiExceptionSubscriber = new ApiExceptionSubscriber($this->logger->reveal());
        $apiExceptionSubscriber->onKernelException($event);
        $this->assertNull($event->getResponse());
    }

    /**
     * @see testOnKernelException
     */
    public function providerOnKernelException(): \Generator
    {
        yield 'random exception' => [new \Exception(), 'General error', 1000000, Response::HTTP_INTERNAL_SERVER_ERROR];

        yield 'random exception but with string code' => [new \Exception('aaa', 1000000), 'General error', 1000000, Response::HTTP_INTERNAL_SERVER_ERROR];

        yield 'http exception' => [new ApiException(), 'Internal server error', 1000001, Response::HTTP_INTERNAL_SERVER_ERROR];

        yield 'symfony http exception' => [new MethodNotAllowedHttpException(['POST']), 'Method Not Allowed', Response::HTTP_METHOD_NOT_ALLOWED, Response::HTTP_METHOD_NOT_ALLOWED];
    }
}
