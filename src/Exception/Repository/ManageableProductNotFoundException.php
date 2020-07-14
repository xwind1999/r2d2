<?php

declare(strict_types=1);

namespace App\Exception\Repository;

class ManageableProductNotFoundException extends EntityNotFoundException
{
    protected const MESSAGE = 'Manageable product not found';
    protected const CODE = 1000022;
}
