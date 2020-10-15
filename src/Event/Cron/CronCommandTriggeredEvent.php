<?php

declare(strict_types=1);

namespace App\Event\Cron;

use App\Event\NamedEventInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Contracts\EventDispatcher\Event;

class CronCommandTriggeredEvent extends Event implements NamedEventInterface
{
    private const EVENT_NAME = 'Cron command triggered';
    private ?string $commandName = null;

    public function __construct(Command $command)
    {
        $this->commandName = $command->getName();
    }

    public function getContext(): array
    {
        return [
            'command_name' => $this->commandName,
        ];
    }

    public function getEventName(): string
    {
        return self::EVENT_NAME;
    }
}
