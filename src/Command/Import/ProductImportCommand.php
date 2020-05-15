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
        'is_sellable',
        'is_reservable',
        'partner',
        'sellable_brand',
        'sellable_country',
        'status',
        'type',
        'product_people_number',
        'voucher_expiration_duration',
    ];

    protected function process(\Iterator $records): void
    {
        foreach ($records as $record) {
            $productRequest = new ProductRequest();
            $universe = new Universe();
            $universe->id = $record['Universe'];

            $partner = new Partner();
            $partner->id = $record['Partner'];

            $sellableBrand = new Brand();
            $sellableBrand->code = $record['SellableBrand'];

            $sellableCountry = new Country();
            $sellableCountry->code = $record['SellableCountry'];

            $productRequest->id = $record['Id'];
            $productRequest->name = $record['Name'];
            $productRequest->description = $record['Description'];
            $productRequest->universe = $universe;
            $productRequest->isSellable = (bool) $record['IsSellable'];
            $productRequest->isReservable = (bool) $record['IsReservable'];
            $productRequest->partner = $partner;
            $productRequest->sellableBrand = $sellableBrand;
            $productRequest->sellableCountry = $sellableCountry;
            $productRequest->status = $record['Status'];
            $productRequest->type = $record['Type'];
            $productRequest->productPeopleNumber = (int) $record['ProductPeopleNumber'];
            $productRequest->voucherExpirationDuration = (int) $record['VoucherExpirationDuration'];

            $errors = $this->validator->validate($productRequest);
            if ($errors->count() > 0) {
                $this->logError($errors);
            }

            $this->messageBus->dispatch($productRequest);
        }
    }
}
