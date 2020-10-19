<?php

declare(strict_types=1);

namespace App\Messenger;

use App\Helper\EaiTransactionId;
use App\Messenger\Stamp\EaiTransactionIdStamp;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

class EaiTransactionIdMiddleware implements MiddlewareInterface
{
    private EaiTransactionId $eaiTransactionId;

    public function __construct(EaiTransactionId $eaiTransactionId)
    {
        $this->eaiTransactionId = $eaiTransactionId;
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $stamp = $envelope->last(EaiTransactionIdStamp::class);

        if (null === $stamp && null !== $this->eaiTransactionId->getTransactionId()) {
            $envelope = $envelope->with(new EaiTransactionIdStamp($this->eaiTransactionId->getTransactionId()));
        } elseif ($stamp instanceof EaiTransactionIdStamp && null !== $stamp->eaiTransactionId) {
            $this->eaiTransactionId->setTransactionIdOverride($stamp->eaiTransactionId);
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
