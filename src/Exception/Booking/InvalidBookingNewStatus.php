<?php

declare(strict_types=1);

namespace App\Exception\Booking;

use App\Exception\Http\UnprocessableEntityException;

class InvalidBookingNewStatus extends UnprocessableEntityException
{
    protected const MESSAGE = 'Invalid booking new status';
    protected const CODE = 1300008;
}
