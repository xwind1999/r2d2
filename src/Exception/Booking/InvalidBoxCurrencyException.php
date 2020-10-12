<?php

declare(strict_types=1);

namespace App\Exception\Booking;

use App\Exception\Http\UnprocessableEntityException;

class InvalidBoxCurrencyException extends UnprocessableEntityException
{
    protected const MESSAGE = 'Invalid box currency';
    protected const CODE = 1300012;
}
