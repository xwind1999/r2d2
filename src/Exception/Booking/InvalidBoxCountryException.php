<?php

declare(strict_types=1);

namespace App\Exception\Booking;

use App\Exception\Http\UnprocessableEntityException;

class InvalidBoxCountryException extends UnprocessableEntityException
{
    protected const MESSAGE = 'Invalid box country';
    protected const CODE = 1300015;
}
