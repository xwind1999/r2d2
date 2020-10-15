<?php

declare(strict_types=1);

namespace App\EventSubscriber\Command;

use App\Command\Cron\CronCommandInterface;
use App\Event\Cron\CronCommandTriggeredEvent;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ConsoleCommandEventSubscriber implements EventSubscriberInterface
{
    protected EventDispatcherInterface $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ConsoleCommandEvent::class => ['consoleCommandEvent', 100],
        ];
    }

    public function consoleCommandEvent(ConsoleCommandEvent $event): void
    {
        $command = $event->getCommand();

        if (null === $command || !$command instanceof CronCommandInterface) {
            return;
        }

        $this->eventDispatcher->dispatch(new CronCommandTriggeredEvent($command));
    }
}
