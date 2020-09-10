<?php

declare(strict_types=1);

namespace App\Event\QuickData;

use App\Event\NamedEventInterface;
use Clogger\ContextualInterface;
use Symfony\Contracts\EventDispatcher\Event;

class BoxCacheErrorEvent extends Event implements ContextualInterface, NamedEventInterface
{
    private const EVENT_NAME = 'QuickData box cache error';

    public string $boxGoldenId;

    public string $date;

    public \Throwable $throwable;

    public function __construct(string $boxGoldenId, string $date, \Throwable $throwable)
    {
        $this->boxGoldenId = $boxGoldenId;
        $this->date = $date;
        $this->throwable = $throwable;
    }

    public function getContext(): array
    {
        return [
            'boxGoldenId' => $this->boxGoldenId,
            'date' => $this->date,
            'exception' => $this->throwable,
        ];
    }

    public function getEventName(): string
    {
        return static::EVENT_NAME;
    }
}
