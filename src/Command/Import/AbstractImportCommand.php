<?php

declare(strict_types=1);

namespace App\Command\Import;

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

abstract class AbstractImportCommand extends Command
{
    protected LoggerInterface $logger;

    protected MessageBusInterface $messageBus;

    protected SymfonyStyle $io;

    protected CSVParser $csvParser;

    protected ValidatorInterface $validator;

    /**
     * @var array
     */
    protected const IMPORT_FIELDS = [];

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
        $service = self::getDefaultName() ?? ':service:';
        preg_match('/:([a-z]+):/', $service, $services);
        $this
            ->setDescription(sprintf('Command to push CSV %s to the queue', $services[1]))
            ->addArgument('file', InputArgument::REQUIRED, 'CSV file path')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        /** @var string $filePath */
        $filePath = $input->getArgument('file');

        $records = $this->csvParser->readFile($filePath, $this::IMPORT_FIELDS);

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

    abstract protected function process(\Iterator $records): void;

    protected function logError(ConstraintViolationListInterface $errors): int
    {
        $msgError = '';
        foreach ($errors as $violation) {
            $msgError = sprintf('Failed during validation :[%s] %s', $violation->getPropertyPath(), $violation->getMessage());
            $this->io->error($msgError);
            $this->logger->error(
                sprintf('Failed during validation :[%s] %s', $violation->getPropertyPath(), $violation->getMessage()),
                $violation->getParameters()
            );
        }
        throw new \InvalidArgumentException($msgError);
    }
}
