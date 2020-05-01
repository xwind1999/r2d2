<?php

declare(strict_types=1);

namespace App\Event\Product;

class BoxBroadcastEvent extends AbstractProductBroadcastEvent
{
    public const EVENT_NAME = 'broadcast.box';

    protected const LOG_MESSAGE = 'Box Broadcast Event';
}
