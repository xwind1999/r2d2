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
        $eaiTransactionId = $this->eaiTransactionId->getTransactionId();

        if (null === $stamp && null !== $eaiTransactionId) {
            $envelope = $envelope->with(new EaiTransactionIdStamp($eaiTransactionId));
        }

        return $stack->next()->handle($envelope, $stack);
    }
}
