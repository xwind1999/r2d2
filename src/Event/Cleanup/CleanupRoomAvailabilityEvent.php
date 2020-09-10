<?php

declare(strict_types=1);

namespace App\Event\Cleanup;

use App\Event\NamedEventInterface;
use Symfony\Contracts\EventDispatcher\Event;

class CleanupRoomAvailabilityEvent extends Event implements NamedEventInterface
{
    private const EVENT_NAME = 'Cleanup Room Availability';

    public function getEventName(): string
    {
        return static::EVENT_NAME;
    }

    public function getContext(): array
    {
        return [];
    }
}
