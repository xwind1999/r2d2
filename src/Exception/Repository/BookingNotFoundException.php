<?php

declare(strict_types=1);

namespace App\Exception\Repository;

class BookingNotFoundException extends EntityNotFoundException
{
    protected const MESSAGE = 'Booking not found';
    protected const CODE = 1000012;
}
