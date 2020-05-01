<?php

declare(strict_types=1);

namespace App\Event\Product;

class ExperienceBroadcastEvent extends AbstractProductBroadcastEvent
{
    public const EVENT_NAME = 'broadcast.experience';

    protected const LOG_MESSAGE = 'Experience Broadcast Event';
}
