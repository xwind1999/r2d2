<?php

declare(strict_types=1);

namespace App\Contract\Request\Room;

use App\Helper\Request\RequestBodyInterface;
use App\Helper\Request\ValidatableRequest;

class RoomUpdateRequest extends RoomCreateRequest implements RequestBodyInterface, ValidatableRequest
{
}
