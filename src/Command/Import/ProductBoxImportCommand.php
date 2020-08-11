<?php

declare(strict_types=1);

namespace App\Command\Import;

class ProductBoxImportCommand extends AbstractProductImportCommand
{
    protected static $defaultName = 'r2d2:product-box:import';

    protected const IMPORT_FIELDS = [
        'id',
        'sellableBrand',
        'sellableCountry',
        'status',
        'listPrice.amount',
        'listPrice.currencyCode',
        'universe.id',
        'type',
        'updatedAt',
    ];
}
