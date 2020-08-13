<?php

declare(strict_types=1);

namespace App\Tests\ApiTests\Helper;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\LegacyEventDispatcherProxy;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\Exception\RejectRedeliveredMessageException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\ConsumedByWorkerStamp;
use Symfony\Component\Messenger\Stamp\ReceivedStamp;
use Symfony\Component\Messenger\Transport\Receiver\ReceiverInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class DisposableWorker
{
    private $receivers;
    private $bus;
    private $eventDispatcher;
    private $shouldStop = false;

    /**
     * @param ReceiverInterface[] $receivers Where the key is the transport name
     */
    public function __construct(array $receivers, MessageBusInterface $bus, EventDispatcherInterface $eventDispatcher = null)
    {
        $this->receivers = $receivers;
        $this->bus = $bus;
        $this->eventDispatcher = class_exists(Event::class) ? LegacyEventDispatcherProxy::decorate($eventDispatcher) : $eventDispatcher;
    }

    public function run(int $count = 1): void
    {
        for ($i = 0; $i < $count; ++$i) {
            $envelopeHandled = false;
            foreach ($this->receivers as $transportName => $receiver) {
                $envelopes = $receiver->get();

                foreach ($envelopes as $envelope) {
                    $envelopeHandled = true;
                    $this->handleMessage($envelope, $receiver, (string) $transportName);

                    if ($this->shouldStop) {
                        break 2;
                    }
                }

                // after handling a single receiver, quit and start the loop again
                // this should prevent multiple lower priority receivers from
                // blocking too long before the higher priority are checked
                if ($envelopeHandled) {
                    break;
                }
            }
        }
    }

    private function handleMessage(Envelope $envelope, ReceiverInterface $receiver, string $transportName): void
    {
        try {
            $envelope = $this->bus->dispatch($envelope->with(new ReceivedStamp($transportName), new ConsumedByWorkerStamp()));
        } catch (\Throwable $throwable) {
            $rejectFirst = $throwable instanceof RejectRedeliveredMessageException;
            if ($rejectFirst) {
                // redelivered messages are rejected first so that continuous failures in an event listener or while
                // publishing for retry does not cause infinite redelivery loops
                $receiver->reject($envelope);
            }

            if ($throwable instanceof HandlerFailedException) {
                $envelope = $throwable->getEnvelope();
            }

            if (!$rejectFirst) {
                $receiver->reject($envelope);
            }

            throw $throwable;
        }

        $receiver->ack($envelope);
    }

    private function dispatchEvent(object $event): void
    {
        if (null === $this->eventDispatcher) {
            return;
        }

        $this->eventDispatcher->dispatch($event);
    }
}
