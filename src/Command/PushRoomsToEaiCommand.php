<?php

declare(strict_types=1);

namespace App\Command;

use App\Contract\Request\EAI\RoomRequest;
use App\Exception\Repository\EntityNotFoundException;
use App\Helper\CSVParser;
use App\Repository\ComponentRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

class PushRoomsToEaiCommand extends Command
{
    protected static $defaultName = 'r2d2:eai:push-rooms';

    private const LOOP_DEFAULT_VALUE = 1;
    private const LINES_QUANTITY = 2;
    private const IMPORT_FIELDS = [
        'golden_id',
    ];

    private CSVParser $csvParser;
    private LoggerInterface $logger;
    private MessageBusInterface $messageBus;
    private ComponentRepository $componentRepository;
    private int $componentsTotal = 0;

    public function __construct(
        CSVParser $csvParser,
        LoggerInterface $logger,
        MessageBusInterface $messageBus,
        ComponentRepository $componentRepository
    ) {
        $this->csvParser = $csvParser;
        $this->logger = $logger;
        $this->messageBus = $messageBus;
        $this->componentRepository = $componentRepository;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Command to send manageable products (components) to EAI')
            ->addArgument('file', InputArgument::REQUIRED, 'CSV file path')
            ->addArgument('batchSize', InputArgument::REQUIRED, 'BATCH SIZE')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);
        /** @var string $filePath */
        $filePath = $input->getArgument('file');
        /** @var string $batchSize */
        $batchSize = $input->getArgument('batchSize');
        $goldenIdList = $this->csvParser->readFile($filePath, static::IMPORT_FIELDS);
        $goldenIdListSize = iterator_count($goldenIdList);
        $symfonyStyle->note(sprintf('Starting at: %s', (new \DateTime())->format('Y-m-d H:i:s')));
        $symfonyStyle->note(sprintf('Total CSV IDs received: %s', $goldenIdListSize));
        $progressBar = new ProgressBar($output, $goldenIdListSize);
        $progressBar->setFormat('debug');
        $progressBar->start();
        $goldenIdArray = [];
        $batchSizeInt = (int) $batchSize;
        try {
            foreach ($goldenIdList as $key => $goldenId) {
                $goldenIdArray[] = $goldenId['golden_id'];
                $intKey = (int) $key;
                if (0 === ($intKey + self::LOOP_DEFAULT_VALUE) % $batchSizeInt) {
                    $offset = ($intKey - $batchSizeInt);
                    $components = $this->componentRepository->findListByGoldenId(
                        array_slice($goldenIdArray, $offset, $batchSizeInt)
                    );
                    $this->componentsTotal += count($components);
                    $this->processComponents($components);
                }
                $progressBar->advance();
            }
        } catch (EntityNotFoundException $exception) {
            $this->logger->error($exception);

            return 1;
        }
        $progressBar->finish();
        $symfonyStyle->newLine(self::LINES_QUANTITY);
        $symfonyStyle->note(sprintf('Total Collection IDs read: %s', $this->componentsTotal));
        $symfonyStyle->note(sprintf('Finishing at : %s', (new \DateTime())->format('Y-m-d H:i:s')));
        $symfonyStyle->success('Command executed');

        return 0;
    }

    private function processComponents(array $components): void
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
