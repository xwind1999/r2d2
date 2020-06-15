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
        'inventory',
        'duration',
        'durationUnit', //TODO: start using this
        'roomStockType',
        'isSellable',
        'isReservable',
        'status',
        'type',
        'updatedAt',
    ];
}
