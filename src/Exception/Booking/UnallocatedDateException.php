<?php

declare(strict_types=1);

namespace App\Exception\Booking;

use App\Exception\Http\UnprocessableEntityException;

class UnallocatedDateException extends UnprocessableEntityException
{
    protected const MESSAGE = 'Unallocated date';
    protected const CODE = 1300003;
}
