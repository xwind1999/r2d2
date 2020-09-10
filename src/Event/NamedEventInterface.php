<?php

declare(strict_types=1);

namespace App\Event;

use Clogger\ContextualInterface;

interface NamedEventInterface extends ContextualInterface
{
    public function getEventName(): string;
}
