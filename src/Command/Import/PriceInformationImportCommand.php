<?php

declare(strict_types=1);

namespace App\Command\Import;

use App\Contract\Request\BroadcastListener\Common\Price;
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

    public function process(\Iterator $records): void
    {
        foreach ($records as $record) {
            $priceInformationRequest = new PriceInformationRequest();

            $product = new Product();
            $product->id = $record['product.id'];

            $amount = $this->moneyHelper->convertToInteger((string) $record['averageValue.amount'], $record['averageValue.currencyCode']);
            $price = new Price();
            $price->currencyCode = $record['averageValue.currencyCode'];
            $price->amount = $amount;
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
