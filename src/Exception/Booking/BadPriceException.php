<?php

declare(strict_types=1);

namespace App\Exception\Booking;

use App\Exception\Http\UnprocessableEntityException;

class BadPriceException extends UnprocessableEntityException
{
    protected const MESSAGE = 'Bad price';
    protected const CODE = 1300001;
}
