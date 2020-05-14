<?php

declare(strict_types=1);

namespace App\Exception\Repository;

class ComponentNotFoundException extends EntityNotFoundException
{
    protected const MESSAGE = 'Component not found';
    protected const CODE = 1000019;
}
