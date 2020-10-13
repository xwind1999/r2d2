<?php

declare(strict_types=1);

namespace App\Exception\Booking;

use App\Exception\Http\UnprocessableEntityException;

class InvalidBoxBrandException extends UnprocessableEntityException
{
    protected const MESSAGE = 'Invalid box brand';
    protected const CODE = 1300014;
}
