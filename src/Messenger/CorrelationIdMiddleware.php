<?php

declare(strict_types=1);

namespace App\Messenger;

use App\Http\CorrelationId\CorrelationId;
use App\Messenger\Stamp\CorrelationIdStamp;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\SentStamp;

class CorrelationIdMiddleware implements MiddlewareInterface
{
    private CorrelationId $correlationId;

    public function __construct(CorrelationId $correlationId)
    {
        $this->correlationId = $correlationId;
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        if (
            null !== $envelope->last(SentStamp::class) &&
            null === $envelope->last(CorrelationIdStamp::class)
        ) {
            $envelope = $envelope->with(new CorrelationIdStamp($this->correlationId->getUuid()));
        }

        return $stack->next()->handle($envelope, $stack);
    }
}
