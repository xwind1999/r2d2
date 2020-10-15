<?php

declare(strict_types=1);

namespace App\Command\Cron;

use App\Event\Cleanup\AvailabilityCleanupEvent;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class CronCleanupRoomAvailabilityCommand extends Command implements CronCommandInterface
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
        $this->eventDispatcher->dispatch(new AvailabilityCleanupEvent());

        return 0;
    }
}
