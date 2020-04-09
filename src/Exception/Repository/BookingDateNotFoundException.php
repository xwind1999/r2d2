<?php

declare(strict_types=1);

namespace App\Exception\Repository;

class BookingDateNotFoundException extends EntityNotFoundException
{
    protected const CODE = 1000011;
}
