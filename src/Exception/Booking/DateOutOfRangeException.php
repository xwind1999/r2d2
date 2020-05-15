<?php

declare(strict_types=1);

namespace App\Exception\Booking;

use App\Exception\Http\UnprocessableEntityException;

class DateOutOfRangeException extends UnprocessableEntityException
{
    protected const MESSAGE = 'Date out of range';
    protected const CODE = 1300002;
}
