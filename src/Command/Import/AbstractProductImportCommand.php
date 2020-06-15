<?php

declare(strict_types=1);

namespace App\Command\Import;

use App\Contract\Request\BroadcastListener\ProductRequest;

abstract class AbstractProductImportCommand extends AbstractImportCommand
{
    protected static $defaultName = 'r2d2:product:import';

    protected function process(\Iterator $records): void
    {
        foreach ($records as $record) {
            $productRequest = ProductRequest::fromArray($record);
            $errors = $this->validator->validate($productRequest);
            if ($errors->count() > 0) {
                $this->logError($errors);
            }

            $this->messageBus->dispatch($productRequest);
        }
    }
}
