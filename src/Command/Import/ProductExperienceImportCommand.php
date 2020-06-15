<?php

declare(strict_types=1);

namespace App\Command\Import;

class ProductExperienceImportCommand extends AbstractProductImportCommand
{
    protected static $defaultName = 'r2d2:product-experience:import';

    protected const IMPORT_FIELDS = [
        'id',
        'partner',
        'name',
        'description',
        'type',
        'status',
        'peopleNumber',
        'updatedAt',
    ];
}
