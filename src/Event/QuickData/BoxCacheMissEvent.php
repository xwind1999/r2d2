<?php

declare(strict_types=1);

namespace App\Event\QuickData;

use App\Event\NamedEventInterface;
use Clogger\ContextualInterface;
use Symfony\Contracts\EventDispatcher\Event;

class BoxCacheMissEvent extends Event implements ContextualInterface, NamedEventInterface
{
    private const EVENT_NAME = 'QuickData box cache miss';

    public string $boxGoldenId;

    public string $date;

    public function __construct(string $boxGoldenId, string $date)
    {
        $this->boxGoldenId = $boxGoldenId;
        $this->date = $date;
    }

    public function getContext(): array
    {
        return [
            'boxGoldenId' => $this->boxGoldenId,
            'date' => $this->date,
        ];
    }

    public function getEventName(): string
    {
        return static::EVENT_NAME;
    }
}
