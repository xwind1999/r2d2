<?php

declare(strict_types=1);

namespace App\Command;

use App\Contract\Request\BroadcastListener\ProductRequest;
use App\Contract\Request\Manageable\ManageableProductRequest;
use Symfony\Component\Console\Input\InputArgument;

class CalculateManageableFlagCommand extends BulkProcessAbstractCommand
{
    protected static $defaultName = 'r2d2:calculate-manageable-flag';

    protected function configure(): void
    {
        $this
            ->setDescription('Command to calculate components manageable flags and push them to EAI from a CSV file')
            ->addArgument('file', InputArgument::REQUIRED, 'CSV file path')
            ->addArgument('batchSize', InputArgument::REQUIRED, 'BATCH SIZE')
        ;
    }

    protected function processComponents(array $components): void
    {
        foreach ($components as $key => $component) {
            $manageableProductRequest = new ManageableProductRequest();
            try {
                $manageableProductRequest->setProductRequest(ProductRequest::fromComponent($component));
                $this->messageBus->dispatch($manageableProductRequest);
            } catch (\Exception $exception) {
                $this->logger->error($exception);
            }
        }
    }
}
