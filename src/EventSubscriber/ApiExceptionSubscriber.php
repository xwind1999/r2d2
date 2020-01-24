<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Contract\Response\ErrorResponse;
use App\Exception\Http\HttpException;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ApiExceptionSubscriber implements EventSubscriberInterface
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $this->logger->error($exception);

        $errorResponse = new ErrorResponse();
        $httpCode = Response::HTTP_INTERNAL_SERVER_ERROR;
        if ($exception instanceof HttpException) {
            $errorResponse
                ->setCode((int) $exception->getCode())
                ->setMessage($exception->getMessage())
                ;
            $httpCode = $exception->getHttpCode();
        }

        $response = new JsonResponse($errorResponse->toArray(), $httpCode);
        $event->setResponse($response);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }
}
