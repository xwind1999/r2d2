<?php

declare(strict_types=1);

namespace App\Command;

use App\Contract\Request\BroadcastListener\ProductRequest;
use App\Contract\Request\Manageable\ManageableProductRequest;
use App\Helper\CSVParser;
use App\Repository\ComponentRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Messenger\MessageBusInterface;

class CalculateManageableFlagCommand extends BulkProcessAbstractCommand
{
    protected static $defaultName = 'r2d2:calculate-manageable-flag';

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
            ->setDescription('Command to calculate components manageable flags and push them to EAI from a CSV file')
            ->addArgument('file', InputArgument::REQUIRED, 'CSV file path')
            ->addArgument('batchSize', InputArgument::REQUIRED, 'BATCH SIZE')
        ;
    }

    protected function process(array $goldenIdList): void
    {
        $components = $this->componentRepository->findListByGoldenId($goldenIdList);
        $this->dataTotal += count($components);

        try {
            foreach ($components as $key => $component) {
                $manageableProductRequest = new ManageableProductRequest();
                $manageableProductRequest->setProductRequest(ProductRequest::fromComponent($component));
                $this->messageBus->dispatch($manageableProductRequest);
            }
        } catch (\Exception $exception) {
            $this->logger->error($exception);
        }
    }
}
