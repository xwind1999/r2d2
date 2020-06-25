<?php

declare(strict_types=1);

namespace App\Exception\Booking;

use App\Exception\Http\UnprocessableEntityException;

class MisconfiguredExperiencePriceException extends UnprocessableEntityException
{
    protected const MESSAGE = 'The experience price must be a number greater than zero';
    protected const CODE = 1300006;
}
