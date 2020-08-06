<?php

declare(strict_types=1);
/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Messenger\EventListener;

use App\Event\NamedEventInterface;
use Clogger\ContextualInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Event\SendMessageToTransportsEvent;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;

class MessengerEventsListener implements EventSubscriberInterface
{
    private const MESSAGE_SENT = 'Message sent';
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
        ];
    }

    public function onMessageFailed(WorkerMessageFailedEvent $event): void
    {
        $envelope = $event->getEnvelope();
        $message = $envelope->getMessage();
        $throwable = $event->getThrowable();

        $context = $this->generateContext($message);

        $this->logger->error($throwable, $context);

        $context += ['exception' => $throwable];

        $this->logger->info(self::ERROR_WHILE_HANDLING_MESSAGE, $context);
    }

    public function onMessageSent(SendMessageToTransportsEvent $event): void
    {
        $envelope = $event->getEnvelope();
        $message = $envelope->getMessage();

        $this->logger->info(self::MESSAGE_SENT, $this->generateContext($message));
    }

    private function generateContext(object $message): array
    {
        return [
            'message' => get_class($message),
            'event_name' => $message instanceof NamedEventInterface ? $message->getEventName() : null,
            'message_parsed' => $message instanceof ContextualInterface ? $message->getContext() : $message,
        ];
    }
}
