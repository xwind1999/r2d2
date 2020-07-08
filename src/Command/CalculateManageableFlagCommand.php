<?php

declare(strict_types=1);

namespace App\Command;

use App\Contract\Request\BroadcastListener\ProductRequest;
use App\Contract\Request\Manageable\ManageableProductRequest;
use App\Exception\Repository\EntityNotFoundException;
use App\Helper\CSVParser;
use App\Repository\ComponentRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

class CalculateManageableFlagCommand extends Command
{
    protected static $defaultName = 'r2d2:calculate-manageable-flag';
    private const IMPORT_FIELDS = [
        'golden_id',
    ];

    private CSVParser $csvParser;
    private LoggerInterface $logger;
    private MessageBusInterface $messageBus;
    private ComponentRepository $componentRepository;

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
            ->setDescription('Command to calculate components manageable flags and push them to EAI from a CSV file')
            ->addArgument('file', InputArgument::REQUIRED, 'CSV file path')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);
        /** @var string $filePath */
        $filePath = $input->getArgument('file');
        $goldenIdArray = $this->transformFromIterator($this->csvParser->readFile($filePath, static::IMPORT_FIELDS));
        $symfonyStyle->note(sprintf('Starting at: %s', (new \DateTime())->format('Y-m-d H:i:s')));
        $symfonyStyle->note(sprintf('Total CSV IDs: %s', count($goldenIdArray)));
        try {
            $components = $this->componentRepository->findListByGoldenId($goldenIdArray);
        } catch (EntityNotFoundException $exception) {
            $this->logger->error($exception);

            return 1;
        }

        $this->processComponents($components);
        $symfonyStyle->note(sprintf('Total Collection IDs: %s', count($components)));
        $symfonyStyle->note(sprintf('Finishing at : %s', (new \DateTime())->format('Y-m-d H:i:s')));
        $symfonyStyle->success('Command executed');

        return 0;
    }

    private function transformFromIterator(\Iterator $goldenIdList): array
    {
        $goldenIdArray = [];
        foreach ($goldenIdList as $goldenId) {
            $goldenIdArray[] = $goldenId['golden_id'];
        }

        return $goldenIdArray;
    }

    private function processComponents(array $components): void
    {
        foreach ($components as $key => $component) {
            $manageableProductRequest = new ManageableProductRequest();
            try {
                $manageableProductRequest->setProductRequest(ProductRequest::transformFromComponent($component));
                $this->messageBus->dispatch($manageableProductRequest);
            } catch (\Exception $exception) {
                $this->logger->error($exception);
            }
        }
    }
}
