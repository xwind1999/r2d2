<?php

declare(strict_types=1);

namespace App\Exception\Booking;

use App\Exception\ContextualException;

class UnallocatedDateException extends ContextualException
{
    protected const MESSAGE = 'Unallocated date';
    protected const CODE = 1300003;
}
