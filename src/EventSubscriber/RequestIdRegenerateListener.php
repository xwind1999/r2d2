<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Logger\Processor\RequestIdProcessor;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Event\WorkerMessageReceivedEvent;

class RequestIdRegenerateListener implements EventSubscriberInterface
{
    private RequestIdProcessor $requestProcessor;

    public function __construct(RequestIdProcessor $requestProcessor)
    {
        $this->requestProcessor = $requestProcessor;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            WorkerMessageReceivedEvent::class => ['regenerateRequestId', 100],
        ];
    }

    public function regenerateRequestId(WorkerMessageReceivedEvent $event): void
    {
        $this->requestProcessor->regenerate();
    }
}
