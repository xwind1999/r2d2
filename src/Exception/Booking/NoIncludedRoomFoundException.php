<?php

declare(strict_types=1);

namespace App\Exception\Booking;

use App\Exception\Http\UnprocessableEntityException;

class NoIncludedRoomFoundException extends UnprocessableEntityException
{
    protected const MESSAGE = 'No included room found';
    protected const CODE = 1300007;
}
