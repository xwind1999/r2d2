<?php

declare(strict_types=1);

namespace App\Exception\Booking;

use App\Exception\ContextualException;

class InvalidExtraNightException extends ContextualException
{
    protected const MESSAGE = 'Invalid extra night';
    protected const CODE = 1300005;
}
