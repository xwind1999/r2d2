<?php

declare(strict_types=1);

namespace App\Command\Import;

class ProductComponentImportCommand extends AbstractProductImportCommand
{
    protected static $defaultName = 'r2d2:product-component:import';

    protected const IMPORT_FIELDS = [
        'id',
        'partner',
        'name',
        'description',
        'stockAllotment',
        'productDuration',
        'productDurationUnit',
        'roomStockType',
        'isSellable',
        'isReservable',
        'status',
        'type',
        'updatedAt',
    ];
}
