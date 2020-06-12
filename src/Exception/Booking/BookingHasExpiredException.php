<?php

declare(strict_types=1);

namespace App\Exception\Booking;

use App\Exception\Http\UnprocessableEntityException;

class BookingHasExpiredException extends UnprocessableEntityException
{
    protected const MESSAGE = 'Booking has expired';
    protected const CODE = 13000010;
}
