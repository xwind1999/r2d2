<?php

declare(strict_types=1);

namespace App\Exception\Booking;

use App\Exception\Http\UnprocessableEntityException;

class MisconfiguredExperiencePriceException extends UnprocessableEntityException
{
    protected const MESSAGE = 'Misconfigured experience price';
    protected const CODE = 1300006;
}
