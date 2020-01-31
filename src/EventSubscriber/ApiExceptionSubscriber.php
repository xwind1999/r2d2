<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Contract\Response\ErrorResponse;
use App\Exception\Http\ApiException;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
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

        if ($exception instanceof ApiException || $exception instanceof HttpExceptionInterface) {
            $httpCode = $exception->getStatusCode();
            $code = 0 === $exception->getCode() ? $httpCode : (int) $exception->getCode();

            $message = $exception->getMessage();
            if ($exception instanceof HttpExceptionInterface) {
                $message = Response::$statusTexts[$httpCode] ?? null;
            }

            $errorResponse
                ->setMessage($message)
                ->setCode($code)
            ;
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
