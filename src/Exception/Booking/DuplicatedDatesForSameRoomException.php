<?php

declare(strict_types=1);

namespace App\Exception\Booking;

use App\Exception\Http\UnprocessableEntityException;

class DuplicatedDatesForSameRoomException extends UnprocessableEntityException
{
    protected const MESSAGE = 'Duplicated dates for same room';
    protected const CODE = 1300009;
}
