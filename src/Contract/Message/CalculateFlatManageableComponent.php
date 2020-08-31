<?php

declare(strict_types=1);

namespace App\Contract\Message;

use App\Event\NamedEventInterface;
use Clogger\ContextualInterface;
use Symfony\Contracts\EventDispatcher\Event;

class CalculateFlatManageableComponent extends Event implements ContextualInterface, NamedEventInterface
{
    public const EVENT_NAME = 'Calculate flat manageable component';

    public string $componentGoldenId;

    public function __construct(string $componentGoldenId)
    {
        $this->componentGoldenId = $componentGoldenId;
    }

    public function getContext(): array
    {
        return [
            'component_golden_id' => $this->componentGoldenId,
        ];
    }

    public function getEventName(): string
    {
        return static::EVENT_NAME;
    }
}
