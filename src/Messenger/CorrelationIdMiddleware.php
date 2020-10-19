<?php

declare(strict_types=1);

namespace App\Messenger;

use App\Http\CorrelationId\CorrelationId;
use App\Messenger\Stamp\CorrelationIdStamp;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

class CorrelationIdMiddleware implements MiddlewareInterface
{
    private CorrelationId $correlationId;

    public function __construct(CorrelationId $correlationId)
    {
        $this->correlationId = $correlationId;
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $stamp = $envelope->last(CorrelationIdStamp::class);

        if (null === $stamp) {
            $envelope = $envelope->with(new CorrelationIdStamp($this->correlationId->getCorrelationId()));
        } elseif ($stamp instanceof CorrelationIdStamp && null !== $stamp->correlationId) {
            $this->correlationId->setCorrelationIdOverride($stamp->correlationId);
        }

        try {
            return $stack->next()->handle($envelope, $stack);
            // @codeCoverageIgnoreStart
        } finally {
            // @codeCoverageIgnoreEnd
            $this->correlationId->resetCorrelationIdOverride();
            $this->correlationId->regenerate();
        }
    }
}
