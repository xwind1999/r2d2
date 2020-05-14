<?php

declare(strict_types=1);

namespace App\Exception\Booking;

use App\Exception\ContextualException;

class BadPriceException extends ContextualException
{
    protected const MESSAGE = 'Bad price';
    protected const CODE = 1300001;
}
