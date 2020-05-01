<?php

declare(strict_types=1);

namespace App\Event;

use App\Helper\LoggableEventInterface;
use Symfony\Contracts\EventDispatcher\Event;

abstract class AbstractLoggableEvent extends Event implements LoggableEventInterface
{
    protected const LOG_MESSAGE = 'log.message';

    protected const LOG_LEVEL = 'info';

    public function getMessage(): string
    {
        return static::LOG_MESSAGE;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getLevel(): string
    {
        return static::LOG_LEVEL;
    }

    abstract public function getContext(): array;
}
