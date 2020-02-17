<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Contract\Response\ErrorResponse;
use App\Exception\Http\ApiException;
use Clogger\ContextualInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class ApiExceptionSubscriber implements EventSubscriberInterface
{
    private LoggerInterface $logger;

    private ParameterBagInterface $parameterBag;

    public function __construct(LoggerInterface $logger, ParameterBagInterface $parameterBag)
    {
        $this->logger = $logger;
        $this->parameterBag = $parameterBag;
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

            if ($exception instanceof ContextualInterface && is_array($exception->getContext()['errors'])) {
                $errorResponse->setErrorList($exception->getContext()['errors']);
            }
        } else if ('prod' !== $this->parameterBag->get('kernel.environment')) {
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
