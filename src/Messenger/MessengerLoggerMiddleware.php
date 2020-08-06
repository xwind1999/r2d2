<?php

declare(strict_types=1);

namespace App\Messenger;

use App\Event\NamedEventInterface;
use Clogger\ContextualInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\ReceivedStamp;

class MessengerLoggerMiddleware implements MiddlewareInterface
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $message = $envelope->getMessage();
        $context = [
            'message' => get_class($message),
            'event_name' => $message instanceof NamedEventInterface ? $message->getEventName() : null,
            'message_parsed' => $message instanceof ContextualInterface ? $message->getContext() : $message,
        ];

        if (null === $envelope->last(ReceivedStamp::class)) {
            $this->logger->info('Message received', $context);
        }

        return $stack->next()->handle($envelope, $stack);
    }
}
