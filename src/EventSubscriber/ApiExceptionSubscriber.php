<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Contract\Response\ErrorResponse;
use App\Exception\Http\ApiException;
use Clogger\ContextualInterface;
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

            $errorResponse->message = $message;
            $errorResponse->code = $code;

            if ($exception instanceof ContextualInterface
                && isset($exception->getContext()['errors'])
                && is_array($exception->getContext()['errors'])
            ) {
                $errorResponse->errorList = $exception->getContext()['errors'];
            }
        } elseif ('prod' !== $event->getRequest()->server->get('APP_ENV', 'prod')) {
            return;
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
