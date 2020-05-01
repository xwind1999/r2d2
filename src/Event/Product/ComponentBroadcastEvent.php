<?php

declare(strict_types=1);

namespace App\Event\Product;

class ComponentBroadcastEvent extends AbstractProductBroadcastEvent
{
    public const EVENT_NAME = 'broadcast.component';

    protected const LOG_MESSAGE = 'Component Broadcast Event';
}
