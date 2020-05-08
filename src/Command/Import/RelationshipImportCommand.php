<?php

declare(strict_types=1);

namespace App\Command\Import;

use App\Contract\Request\BroadcastListener\ProductRelationshipRequest;
use App\Helper\CSVParser;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RelationshipImportCommand extends Command
{
    protected static $defaultName = 'r2d2:relationship:import';

    private const IMPORT_FIELDS = [
        'parentProduct',
        'childProduct',
        'sortOrder',
        'isEnabled',
        'relationshipType',
        'printType',
        'childCount',
        'childQuantity',
    ];
    private LoggerInterface $logger;
    private MessageBusInterface $messageBus;
    private SymfonyStyle $io;
    private CSVParser $csvParser;
    private ValidatorInterface $validator;

    public function __construct(
        LoggerInterface $logger,
        MessageBusInterface $messageBus,
        CSVParser $csvParser,
        ValidatorInterface $validator
    ) {
        $this->logger = $logger;
        $this->messageBus = $messageBus;
        $this->csvParser = $csvParser;
        $this->validator = $validator;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Command to push CSV relationships to the queue')
            ->addArgument('file', InputArgument::REQUIRED, 'CSV file path')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        /** @var string $filePath */
        $filePath = $input->getArgument('file');
        $records = $this->csvParser->readFile($filePath, self::IMPORT_FIELDS);

        $this->io->note(sprintf('Total records: %s', iterator_count($records)));
        $this->io->note(sprintf('Starting at: %s', (new \DateTime())->format('Y-m-d H:i:s')));

        try {
            $this->process($records);
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
            $this->io->error('Command exited');

            return 1;
        }

        $this->io->note(sprintf('Finishing at : %s', (new \DateTime())->format('Y-m-d H:i:s')));
        $this->io->success('Command executed');

        return 0;
    }

    private function process(\Iterator $records): void
    {
        foreach ($records as $record) {
            $productRelationshipRequest = new ProductRelationshipRequest();

            $productRelationshipRequest->parentProduct = $record['parentProduct'];
            $productRelationshipRequest->childProduct = $record['childProduct'];
            $productRelationshipRequest->sortOrder = (int) $record['sortOrder'];
            $productRelationshipRequest->isEnabled = (bool) $record['isEnabled'];
            $productRelationshipRequest->relationshipType = $record['relationshipType'];
            $productRelationshipRequest->printType = $record['printType'];
            $productRelationshipRequest->childCount = (int) $record['childCount'];
            $productRelationshipRequest->childQuantity = (int) $record['childQuantity'];

            $errors = $this->validator->validate($productRelationshipRequest);
            if (count($errors) > 0) {
                $this->logError($errors, $productRelationshipRequest);

                continue;
            }

            $this->messageBus->dispatch($productRelationshipRequest);
        }
    }

    private function logError(ConstraintViolationListInterface $errors, ProductRelationshipRequest $productRelationshipRequest): void
    {
        /** @psalm-suppress InvalidCast */
        $errors = (string) $errors; /* @phpstan-ignore-line */
        $this->io->error($errors);
        $this->logger->error($errors, $productRelationshipRequest->getContext());
    }
}
