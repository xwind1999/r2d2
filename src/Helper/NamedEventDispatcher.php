<?php

declare(strict_types=1);

namespace App\Helper;

use App\Event\NamedEventInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class NamedEventDispatcher implements EventDispatcherInterface
{
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function dispatch(object $event, ?string $eventName = null): object
    {
        if ($event instanceof NamedEventInterface) {
            $this->eventDispatcher->dispatch($event, NamedEventInterface::class);
        }

        $this->eventDispatcher->dispatch($event, $eventName);

        return $event;
    }
}
