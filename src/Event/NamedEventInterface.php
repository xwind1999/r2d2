<?php

declare(strict_types=1);

namespace App\Event;

interface NamedEventInterface
{
    public function getEventName(): string;
}
