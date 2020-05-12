<?php

declare(strict_types=1);

namespace App\Command\Import;

use App\Contract\Request\BroadcastListener\PartnerRequest;
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

class PartnerImportCommand extends Command
{
    protected static $defaultName = 'r2d2:partner:import';

    private const IMPORT_FIELDS = [
        'Account_URN__c',
        'Type',
        'CurrencyIsoCode',
        'CeaseDate__c',
        'Channel_Manager_Active__c',
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
            ->setDescription('Command to push CSV partners to the queue')
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
            $partnerRequest = new PartnerRequest();
            $partnerRequest->id = $record['Account_URN__c'];
            $partnerRequest->status = 'active';
            $partnerRequest->currencyCode = $record['CurrencyIsoCode'];
            $partnerRequest->partnerCeaseDate = $record['CeaseDate__c'];
            $partnerRequest->isChannelManagerEnabled = $record['Channel_Manager_Active__c'];

            $errors = $this->validator->validate($partnerRequest);
            if (count($errors) > 0) {
                $this->logError($errors, $partnerRequest);

                continue;
            }

            $this->messageBus->dispatch($partnerRequest);
        }
    }

    private function logError(ConstraintViolationListInterface $errors, PartnerRequest $partnerRequest): void
    {
        /** @psalm-suppress InvalidCast */
        $errors = (string) $errors; /* @phpstan-ignore-line */
        $this->io->error($errors);
        $this->logger->error($errors, $partnerRequest->getContext());
    }
}
