<?php

declare(strict_types=1);

namespace App\Contract\Message;

use App\Event\NamedEventInterface;
use Symfony\Contracts\EventDispatcher\Event;

class InvalidAvailabilityCleanup extends Event implements NamedEventInterface
{
    public const EVENT_NAME = 'Invalid availability cleanup';

    public function getContext(): array
    {
        return [];
    }

    public function getEventName(): string
    {
        return static::EVENT_NAME;
    }
}
