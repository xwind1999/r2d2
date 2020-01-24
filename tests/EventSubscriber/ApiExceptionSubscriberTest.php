<?php

declare(strict_types=1);

namespace App\Tests\EventSubscriber;

use App\EventSubscriber\ApiExceptionSubscriber;
use App\Exception\Http\HttpException;
use App\Kernel;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ApiExceptionSubscriberTest extends TestCase
{
    /**
     * @var LoggerInterface|\Prophecy\Prophecy\ObjectProphecy
     */
    protected $logger;

    public function setUp(): void
    {
        $this->logger = $this->prophesize(LoggerInterface::class);
    }

    public function testGetSubscribedEvents(): void
    {
        $this->assertEquals([KernelEvents::EXCEPTION => 'onKernelException'], ApiExceptionSubscriber::getSubscribedEvents());
    }

    /**
     * @dataProvider providerOnKernelException
     */
    public function testOnKernelException(\Exception $exception, string $message, int $code, int $httpCode): void
    {
        $event = new ExceptionEvent(
            new Kernel('test', false),
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
     * @see testOnKernelException
     */
    public function providerOnKernelException(): \Generator
    {
        yield 'random exception' => [new \Exception(), 'General error', 1000000, Response::HTTP_INTERNAL_SERVER_ERROR];

        yield 'http exception' => [new HttpException(), 'Internal server error', 1000001, Response::HTTP_INTERNAL_SERVER_ERROR];
    }
}
