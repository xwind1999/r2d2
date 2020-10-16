<?php

declare(strict_types=1);

namespace App\Exception\Booking;

use App\Exception\Http\UnprocessableEntityException;

class InvalidExperienceComponentListException extends UnprocessableEntityException
{
    protected const MESSAGE = 'Invalid experience components';
    protected const CODE = 1300016;
}
