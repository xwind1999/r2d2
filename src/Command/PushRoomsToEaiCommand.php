<?php

declare(strict_types=1);

namespace App\Command;

use App\Contract\Request\EAI\RoomRequest;
use Symfony\Component\Console\Input\InputArgument;

class PushRoomsToEaiCommand extends BulkProcessAbstractCommand
{
    protected static $defaultName = 'r2d2:eai:push-rooms';

    protected function configure(): void
    {
        $this
            ->setDescription('Command to send manageable products (components) to EAI')
            ->addArgument('file', InputArgument::REQUIRED, 'CSV file path')
            ->addArgument('batchSize', InputArgument::REQUIRED, 'BATCH SIZE')
        ;
    }

    protected function processComponents(array $components): void
    {
        foreach ($components as $key => $component) {
            try {
                $roomRequest = RoomRequest::transformFromComponent($component);
                $this->messageBus->dispatch($roomRequest);
            } catch (\Exception $exception) {
                $this->logger->error($exception);
            }
        }
    }
}
