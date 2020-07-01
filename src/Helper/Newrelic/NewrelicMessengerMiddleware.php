<?php

declare(strict_types=1);

namespace App\Helper\Newrelic;

use Ekino\NewRelicBundle\NewRelic\NewRelicInteractorInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\ReceivedStamp;

class NewrelicMessengerMiddleware implements MiddlewareInterface
{
    private NewRelicInteractorInterface $newRelicInteractor;

    public function __construct(NewRelicInteractorInterface $newRelicInteractor)
    {
        $this->newRelicInteractor = $newRelicInteractor;
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        if (null === $envelope->last(ReceivedStamp::class)) {
            return $stack->next()->handle($envelope, $stack);
        }

        $this->newRelicInteractor->startTransaction();
        $this->newRelicInteractor->setTransactionName(get_class($envelope->getMessage()));

        try {
            return $stack->next()->handle($envelope, $stack);
            // @codeCoverageIgnoreStart
        } finally {
            // @codeCoverageIgnoreEnd
            $this->newRelicInteractor->endTransaction();
        }
    }
}
