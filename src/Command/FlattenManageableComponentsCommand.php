<?php

declare(strict_types=1);

namespace App\Command;

use App\Contract\Message\CalculateFlatManageableComponent;
use App\Repository\ComponentRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class FlattenManageableComponentsCommand extends Command
{
    protected static $defaultName = 'r2d2:flatten:manageable-components';

    private MessageBusInterface $messageBus;

    private ComponentRepository $componentRepository;

    public function __construct(
        MessageBusInterface $messageBus,
        ComponentRepository $componentRepository
    ) {
        $this->messageBus = $messageBus;
        $this->componentRepository = $componentRepository;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Command to populate the flat manageable components table')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $components = $this->componentRepository->getAllManageableGoldenIds();

        foreach ($components as $componentId) {
            $message = new CalculateFlatManageableComponent($componentId);
            $this->messageBus->dispatch($message);
        }

        return 0;
    }
}
