<?php

declare(strict_types=1);

namespace App\Helper;

use Clogger\ContextualInterface;

interface LoggableEventInterface extends ContextualInterface
{
    public function getMessage(): string;
}
