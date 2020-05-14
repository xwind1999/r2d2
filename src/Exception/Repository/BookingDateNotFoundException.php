<?php

declare(strict_types=1);

namespace App\Exception\Repository;

class BookingDateNotFoundException extends EntityNotFoundException
{
    protected const MESSAGE = 'Booking date not found';
    protected const CODE = 1000011;
}
