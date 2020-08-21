<?php

declare(strict_types=1);

namespace App\Command\Import;

use App\Contract\Request\BroadcastListener\Product\Product;
use App\Contract\Request\BroadcastListener\RoomAvailabilityRequest;

class RoomAvailabilityImportCommand extends AbstractImportCommand
{
    protected static $defaultName = 'r2d2:room-availability:import';

    protected const IMPORT_FIELDS = [
        'product.id',
        'quantity',
        'dateFrom',
        'dateTo',
        'isStopSale',
        'updatedAt',
    ];

    protected function configure(): void
    {
        parent::configure();
    }

    protected function process(\Iterator $records): void
    {
        foreach ($records as $record) {
            $roomAvailabilityRequest = new RoomAvailabilityRequest();

            $product = new Product();
            $product->id = $record['product.id'];

            $roomAvailabilityRequest->product = $product;
            $roomAvailabilityRequest->quantity = (int) $record['quantity'];
            $roomAvailabilityRequest->dateFrom = new \DateTime($record['dateFrom']);
            $roomAvailabilityRequest->dateTo = new \DateTime($record['dateTo']);

            if (!empty($record['isStopSale'])) {
                $roomAvailabilityRequest->isStopSale = (bool) $record['isStopSale'];
            }

            if (!empty($record['updatedAt'])) {
                $roomAvailabilityRequest->updatedAt = new \DateTime($record['updatedAt']);
            }
            $errors = $this->validator->validate($roomAvailabilityRequest);
            if ($errors->count() > 0) {
                $this->logError($errors);
            }

            $this->messageBus->dispatch($roomAvailabilityRequest);
        }
    }
}
