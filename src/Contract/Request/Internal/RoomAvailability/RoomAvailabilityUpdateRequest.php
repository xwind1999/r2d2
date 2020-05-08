<?php

declare(strict_types=1);

namespace App\Contract\Request\Internal\RoomAvailability;

use App\Helper\Request\RequestBodyInterface;
use App\Helper\Request\ValidatableRequest;

class RoomAvailabilityUpdateRequest extends RoomAvailabilityCreateRequest implements RequestBodyInterface, ValidatableRequest
{
}
