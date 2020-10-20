<?php

declare(strict_types=1);

namespace App\Messenger\EventListener;

use App\Helper\EaiTransactionId;
use App\Http\CorrelationId\CorrelationId;
use App\Messenger\Stamp\CorrelationIdStamp;
use App\Messenger\Stamp\EaiTransactionIdStamp;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use Symfony\Component\Messenger\Event\WorkerMessageHandledEvent;
use Symfony\Component\Messenger\Event\WorkerMessageReceivedEvent;

class MessengerHeaderLoggingListener implements EventSubscriberInterface
{
    private EaiTransactionId $eaiTransactionId;

    private CorrelationId $correlationId;

    public function __construct(EaiTransactionId $eaiTransactionId, CorrelationId $correlationId)
    {
        $this->eaiTransactionId = $eaiTransactionId;
        $this->correlationId = $correlationId;
    }

    public static function getSubscribedEvents()
    {
        return [
            WorkerMessageFailedEvent::class => ['onMessageFailed', 100], //when handling failed
            WorkerMessageHandledEvent::class => ['onMessageHandled', 100],
            WorkerMessageReceivedEvent::class => ['onMessageReceived', 100],
        ];
    }

    public function onMessageFailed(WorkerMessageFailedEvent $event): void
    {
        $this->resetOverrides();
    }

    public function onMessageHandled(WorkerMessageHandledEvent $event): void
    {
        $this->resetOverrides();
    }

    public function onMessageReceived(WorkerMessageReceivedEvent $event): void
    {
        $envelope = $event->getEnvelope();
        $eaiTransactionIdStamp = $envelope->last(EaiTransactionIdStamp::class);

        if ($eaiTransactionIdStamp instanceof EaiTransactionIdStamp) {
            $this->eaiTransactionId->setTransactionIdOverride($eaiTransactionIdStamp->eaiTransactionId);
        }

        $correlationIdStamp = $envelope->last(CorrelationIdStamp::class);

        if ($correlationIdStamp instanceof CorrelationIdStamp) {
            $this->correlationId->setCorrelationIdOverride($correlationIdStamp->correlationId);
        }
    }

    private function resetOverrides(): void
    {
        $this->eaiTransactionId->resetTransactionIdOverride();
        $this->correlationId->resetCorrelationIdOverride();
        $this->correlationId->regenerate();
    }
}
