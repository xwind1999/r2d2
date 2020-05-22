<?php

declare(strict_types=1);

namespace App\Exception\Booking;

use App\Exception\Http\UnprocessableEntityException;

class BookingAlreadyInFinalStatusException extends UnprocessableEntityException
{
    protected const MESSAGE = 'Booking already in final status';
    protected const CODE = 1300009;
}
