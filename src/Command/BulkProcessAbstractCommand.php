<?php

declare(strict_types=1);

namespace App\Command;

use App\Exception\Repository\ComponentNotFoundException;
use App\Exception\Repository\EntityNotFoundException;
use App\Helper\CSVParser;
use App\Repository\ComponentRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

abstract class BulkProcessAbstractCommand extends Command
{
    protected const LINES_QUANTITY = 2;
    protected const IMPORT_FIELDS = [
        'golden_id',
    ];

    protected CSVParser $csvParser;
    protected LoggerInterface $logger;
    protected MessageBusInterface $messageBus;
    protected ComponentRepository $componentRepository;
    protected int $componentsTotal = 0;

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

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);
        /** @var string $filePath */
        $filePath = $input->getArgument('file');
        $batchSize = (int) $input->getArgument('batchSize');  // @phpstan-ignore-line
        $goldenIdList = $this->csvParser->readFile($filePath, static::IMPORT_FIELDS);
        $goldenIdListSize = iterator_count($goldenIdList);
        $symfonyStyle->note(sprintf('Starting at: %s', (new \DateTime())->format('Y-m-d H:i:s')));
        $symfonyStyle->note(sprintf('Total CSV IDs received: %s', $goldenIdListSize));
        $progressBar = new ProgressBar($output, $goldenIdListSize);
        $progressBar->setFormat('debug');
        $progressBar->start();
        $goldenIdArray = [];
        try {
            foreach ($goldenIdList as $key => $goldenId) {
                $intKey = (int) $key;
                $goldenIdArray[] = $goldenId['golden_id'];
                if (0 === $intKey % $batchSize) {
                    $this->searchForComponents(array_slice($goldenIdArray, ($intKey - $batchSize), $batchSize));
                }
                $progressBar->advance();
            }

            if ($this->componentsTotal < count($goldenIdArray)) {
                $this->searchForComponents(array_slice($goldenIdArray, $this->componentsTotal, $batchSize));
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

    /**
     * @throws ComponentNotFoundException
     */
    private function searchForComponents(array $componentsIds): void
    {
        $components = $this->componentRepository->findListByGoldenId($componentsIds);
        $this->componentsTotal += count($components);
        $this->processComponents($components);
    }

    abstract protected function processComponents(array $components): void;
}
