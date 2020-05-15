<?php

declare(strict_types=1);

namespace App\Exception\Booking;

use App\Exception\Http\UnprocessableEntityException;

class RoomsDontHaveSameDurationException extends UnprocessableEntityException
{
    protected const MESSAGE = 'Rooms dont have same duration';
    protected const CODE = 1300004;
}
