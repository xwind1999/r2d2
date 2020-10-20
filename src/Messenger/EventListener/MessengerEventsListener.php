<?php

declare(strict_types=1);

namespace App\Messenger\EventListener;

use App\Event\NamedEventInterface;
use App\Helper\EaiTransactionId;
use App\Http\CorrelationId\CorrelationId;
use App\Messenger\Stamp\CorrelationIdStamp;
use App\Messenger\Stamp\EaiTransactionIdStamp;
use Clogger\ContextualInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Event\SendMessageToTransportsEvent;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use Symfony\Component\Messenger\Event\WorkerMessageHandledEvent;
use Symfony\Component\Messenger\Event\WorkerMessageReceivedEvent;

class MessengerEventsListener implements EventSubscriberInterface
{
    private const MESSAGE_SENT = 'Message sent';
    private const MESSAGE_HANDLED = 'Message handled';
    private const MESSAGE_RECEIVED = 'Message received';
    private const ERROR_WHILE_HANDLING_MESSAGE = 'Error while handling message';

    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public static function getSubscribedEvents()
    {
        return [
            WorkerMessageFailedEvent::class => ['onMessageFailed', 100],
            SendMessageToTransportsEvent::class => ['onMessageSent', 100],
            WorkerMessageHandledEvent::class => ['onMessageHandled', 100],
            WorkerMessageReceivedEvent::class => ['onMessageReceived', 100],
        ];
    }

    public function onMessageFailed(WorkerMessageFailedEvent $event): void
    {
        $envelope = $event->getEnvelope();
        $context = $this->generateContext($envelope);
        $throwable = $event->getThrowable();

        $this->logger->error($throwable, $context);

        $context += ['exception' => $throwable];

        $this->logger->info(self::ERROR_WHILE_HANDLING_MESSAGE, $context);
    }

    public function onMessageSent(SendMessageToTransportsEvent $event): void
    {
        $envelope = $event->getEnvelope();

        $this->logger->info(self::MESSAGE_SENT, $this->generateContext($envelope));
    }

    public function onMessageHandled(WorkerMessageHandledEvent $event): void
    {
        $envelope = $event->getEnvelope();

        $this->logger->info(self::MESSAGE_HANDLED, $this->generateContext($envelope));
    }

    public function onMessageReceived(WorkerMessageReceivedEvent $event): void
    {
        $envelope = $event->getEnvelope();

        $this->logger->info(self::MESSAGE_RECEIVED, $this->generateContext($envelope));
    }

    private function generateContext(Envelope $envelope): array
    {
        $message = $envelope->getMessage();
        $context = [
            'message' => get_class($message),
            'event_name' => $message instanceof NamedEventInterface ? $message->getEventName() : null,
            'message_parsed' => $message instanceof ContextualInterface ? $message->getContext() : $message,
        ];

        if (null !== $envelope->last(EaiTransactionIdStamp::class)) {
            /** @var EaiTransactionIdStamp $eaiTransactionIdStamp */
            $eaiTransactionIdStamp = $envelope->last(EaiTransactionIdStamp::class);
            $context[EaiTransactionId::LOG_FIELD] = $eaiTransactionIdStamp->eaiTransactionId;
        }

        if (null !== $envelope->last(CorrelationIdStamp::class)) {
            /** @var CorrelationIdStamp $correlationIdStamp */
            $correlationIdStamp = $envelope->last(CorrelationIdStamp::class);
            $context[CorrelationId::LOG_FIELD] = $correlationIdStamp->correlationId;
        }

        return $context;
    }
}
