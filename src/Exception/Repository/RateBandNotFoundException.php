<?php

declare(strict_types=1);

namespace App\Exception\Repository;

class RateBandNotFoundException extends EntityNotFoundException
{
    protected const CODE = 1000017;
}
