<?php

declare(strict_types=1);

namespace App\Contract\Request\Internal\BookingDate;

use App\Helper\Request\RequestBodyInterface;
use App\Helper\Request\ValidatableRequest;

class BookingDateUpdateRequest extends BookingDateCreateRequest implements RequestBodyInterface, ValidatableRequest
{
}
