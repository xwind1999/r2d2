<?php

declare(strict_types=1);

namespace App\Exception\Booking;

use App\Exception\Http\UnprocessableEntityException;

class UnavailableDateException extends UnprocessableEntityException
{
    protected const MESSAGE = 'Unavailable date for booking';
    protected const CODE = 1300016;
}
