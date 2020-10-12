<?php

declare(strict_types=1);

namespace App\Command;

use Nelmio\ApiDocBundle\ApiDocGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ApiDumpCommand extends Command
{
    protected const JSON_NO_OPTION = 0;
    protected static $defaultName = 'r2d2:api:dump';

    private ApiDocGenerator $generatorLocator;

    public function __construct(ApiDocGenerator $generatorLocator)
    {
        $this->generatorLocator = $generatorLocator;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Dumps API documentation in Swagger JSON format')
            ->addOption('--no-pretty', '', InputOption::VALUE_NONE, 'Do not pretty format output')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $spec = $this->generatorLocator->generate()->toJson(
            $input->hasParameterOption(['--no-pretty']) ? static::JSON_NO_OPTION : JSON_PRETTY_PRINT
        );

        if (!$spec) {
            return 1;
        }

        $output->writeln($spec);

        return 0;
    }
}
