<?php

declare(strict_types=1);

namespace App\Exception\Booking;

use App\Exception\Http\UnprocessableEntityException;

class ExperienceCurrencyMismatchException extends UnprocessableEntityException
{
    protected const MESSAGE = 'Experience currency mismatch';
    protected const CODE = 1300012;
}
