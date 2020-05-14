<?php

declare(strict_types=1);

namespace App\Exception\Repository;

use App\Exception\Http\ResourceNotFoundException;

abstract class EntityNotFoundException extends ResourceNotFoundException
{
    protected const MESSAGE = 'Entity not found';
    protected const CODE = 1000010;
}
