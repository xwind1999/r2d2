<?php

declare(strict_types=1);

namespace App\Exception\Booking;

use App\Exception\ContextualException;

class DuplicatedDatesForSameRoomException extends ContextualException
{
    protected const MESSAGE = 'Duplicated dates for same room';
    protected const CODE = 1300005;
}
