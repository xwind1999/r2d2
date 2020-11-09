<?php

declare(strict_types=1);

namespace App\Command;

use App\Contract\Message\InvalidAvailabilityCleanup;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class DeleteInvalidRoomAvailabilityCommand extends Command
{
    protected static $defaultName = 'r2d2:cleanup:invalid-room-availability';

    private MessageBusInterface $messageBus;

    public function __construct(MessageBusInterface $messageBus)
    {
        $this->messageBus = $messageBus;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Starts deleting invalid room availabilities')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->messageBus->dispatch(new InvalidAvailabilityCleanup());

        return 0;
    }
}
