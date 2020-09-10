<?php

declare(strict_types=1);

namespace App\Exception\Booking;

use App\Exception\Http\UnprocessableEntityException;

class CurrencyMismatchException extends UnprocessableEntityException
{
    protected const MESSAGE = 'Currency mismatch';
    protected const CODE = 1300011;
}
