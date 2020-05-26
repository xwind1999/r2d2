<?php

declare(strict_types=1);

namespace App\Command\Import;

use App\Contract\Request\BroadcastListener\Product\Brand;
use App\Contract\Request\BroadcastListener\Product\Country;
use App\Contract\Request\BroadcastListener\Product\Partner;
use App\Contract\Request\BroadcastListener\Product\Universe;
use App\Contract\Request\BroadcastListener\ProductRequest;

class ProductImportCommand extends AbstractImportCommand
{
    protected static $defaultName = 'r2d2:product:import';

    protected const IMPORT_FIELDS = [
        'id',
        'name',
        'description',
        'universe',
        'sellable_brand',
        'sellable_country',
        'partner',
        'status',
        'type',
        'product_people_number',
        'is_reservable',
        'is_sellable',
        'voucher_expiration_duration',
    ];

    protected function process(\Iterator $records): void
    {
        foreach ($records as $record) {
            $productRequest = new ProductRequest();

            $productRequest->id = $record['id'];
            $productRequest->name = $record['name'];
            $productRequest->description = strlen($record['description']) > 1 ? $record['description'] : ' ';
            $productRequest->universe = !empty($record['universe']) ? Universe::create($record['universe']) : null;
            $productRequest->partner = !empty($record['partner']) ? Partner::create($record['partner']) : null;
            $productRequest->sellableBrand = !empty($record['sellableBrand']) ? Brand::create($record['sellableBrand']) : null;
            $productRequest->sellableCountry = !empty($record['sellableCountryCode']) ? Country::create($record['sellableCountry']) : null;
            $productRequest->isSellable = (bool) $record['is_sellable'];
            $productRequest->isReservable = (bool) $record['is_reservable'];
            $productRequest->status = $record['status'];
            $productRequest->type = $record['type'];
            $productRequest->productPeopleNumber = (int) $record['product_people_number'];
            $productRequest->voucherExpirationDuration = (int) $record['voucher_expiration_duration'];

            $errors = $this->validator->validate($productRequest);
            if ($errors->count() > 0) {
                $this->logError($errors);
            }

            $this->messageBus->dispatch($productRequest);
        }
    }
}
