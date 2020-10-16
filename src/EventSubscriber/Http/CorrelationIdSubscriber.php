<?php

declare(strict_types=1);

namespace App\EventSubscriber\Http;

use App\Http\CorrelationId\CorrelationId;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class CorrelationIdSubscriber implements EventSubscriberInterface
{
    private CorrelationId $correlationId;

    public function __construct(CorrelationId $correlationId)
    {
        $this->correlationId = $correlationId;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => [
                ['onKernelResponse', 255],
            ],
        ];
    }

    public function onKernelResponse(ResponseEvent $responseEvent): void
    {
        if (!$responseEvent->getResponse()->headers->get(CorrelationId::HEADER_KEY)) {
            $responseEvent->getResponse()->headers->set(CorrelationId::HEADER_KEY, $this->correlationId->getCorrelationId());
        }
    }
}
