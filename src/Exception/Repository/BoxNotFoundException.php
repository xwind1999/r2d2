<?php

declare(strict_types=1);

namespace App\Exception\Repository;

class BoxNotFoundException extends EntityNotFoundException
{
    protected const MESSAGE = 'Box not found';
    protected const CODE = 1000013;
}
