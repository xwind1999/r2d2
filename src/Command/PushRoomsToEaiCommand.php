<?php

declare(strict_types=1);

namespace App\Command;

use App\Contract\Request\EAI\RoomRequest;
use App\Helper\CSVParser;
use App\Repository\ComponentRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Messenger\MessageBusInterface;

class PushRoomsToEaiCommand extends BulkProcessAbstractCommand
{
    protected static $defaultName = 'r2d2:eai:push-rooms';

    private ComponentRepository $componentRepository;

    public function __construct(
        CSVParser $csvParser,
        LoggerInterface $logger,
        MessageBusInterface $messageBus,
        ComponentRepository $componentRepository
    ) {
        $this->componentRepository = $componentRepository;
        parent::__construct($csvParser, $logger, $messageBus);
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Command to send manageable products (components) to EAI')
            ->addArgument('file', InputArgument::REQUIRED, 'CSV file path')
            ->addArgument('batchSize', InputArgument::REQUIRED, 'BATCH SIZE')
        ;
    }

    protected function process(array $goldenIdList): void
    {
        $components = $this->componentRepository->findListByGoldenId($goldenIdList);
        $this->dataTotal += count($components);
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
