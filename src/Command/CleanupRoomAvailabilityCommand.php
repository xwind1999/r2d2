<?php

declare(strict_types=1);

namespace App\Command;

use App\Event\Cleanup\CleanupRoomAvailabilityEvent;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class CleanupRoomAvailabilityCommand extends Command
{
    protected static $defaultName = 'r2d2:cleanup:room-availability';

    private EventDispatcherInterface $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Run room-availability cleanup routines')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $event = new CleanupRoomAvailabilityEvent();
        $this->eventDispatcher->dispatch($event);

        return 0;
    }
}
