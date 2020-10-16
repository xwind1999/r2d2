<?php

declare(strict_types=1);

namespace App\Messenger;

use App\Helper\EaiTransactionId;
use App\Messenger\Stamp\EaiTransactionIdStamp;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\SentStamp;

class EaiTransactionIdMiddleware implements MiddlewareInterface
{
    private EaiTransactionId $eaiTransactionId;

    public function __construct(EaiTransactionId $eaiTransactionId)
    {
        $this->eaiTransactionId = $eaiTransactionId;
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        if (
            null !== $envelope->last(SentStamp::class) &&
            null === $envelope->last(EaiTransactionIdStamp::class)
        ) {
            $envelope = $envelope->with(new EaiTransactionIdStamp($this->eaiTransactionId->getTransactionId()));
        } else {
            $stamp = $envelope->last(EaiTransactionIdStamp::class);

            if ($stamp instanceof EaiTransactionIdStamp && null !== $stamp->eaiTransactionId) {
                $this->eaiTransactionId->setTransactionIdOverride($stamp->eaiTransactionId);
            }
        }

        try {
            return $stack->next()->handle($envelope, $stack);
            // @codeCoverageIgnoreStart
        } finally {
            // @codeCoverageIgnoreEnd
            $this->eaiTransactionId->resetTransactionIdOverride();
        }
    }
}
