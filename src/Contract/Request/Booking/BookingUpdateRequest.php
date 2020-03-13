<?php

declare(strict_types=1);

namespace App\Contract\Request\Booking;

use App\Helper\Request\RequestBodyInterface;
use App\Helper\Request\ValidatableRequest;

class BookingUpdateRequest extends BookingCreateRequest implements RequestBodyInterface, ValidatableRequest
{
}
