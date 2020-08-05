<?php

declare(strict_types=1);

namespace App\Command\Import;

use App\Contract\Request\BroadcastListener\PriceInformation\Price;
use App\Contract\Request\BroadcastListener\PriceInformationRequest;
use App\Contract\Request\BroadcastListener\Product\Product;

class PriceInformationImportCommand extends AbstractImportCommand
{
    protected static $defaultName = 'r2d2:price-information:import';

    protected const IMPORT_FIELDS = [
        'product.id',
        'averageValue.amount',
        'averageValue.currencyCode',
        'averageCommissionType',
        'averageCommission',
        'updatedAt',
    ];

    protected function process(\Iterator $records): void
    {
        foreach ($records as $record) {
            $priceInformationRequest = new PriceInformationRequest();

            $product = new Product();
            $product->id = $record['product.id'];

            $price = new Price();
            $price->currencyCode = $record['averageValue.currencyCode'];
            $price->amount = (int) $record['averageValue.amount'] * 100;

            $priceInformationRequest->product = $product;
            $priceInformationRequest->averageValue = $price;
            $priceInformationRequest->averageCommission = $record['averageCommission'];

            if (!empty($record['averageCommissionType'])) {
                $priceInformationRequest->averageCommissionType = $record['averageCommissionType'];
            }

            if (!empty($record['updatedAt'])) {
                $priceInformationRequest->updatedAt = new \DateTime($record['updatedAt']);
            }

            $errors = $this->validator->validate($priceInformationRequest);
            if ($errors->count() > 0) {
                $this->logError($errors);
            }

            $this->messageBus->dispatch($priceInformationRequest);
        }
    }
}
