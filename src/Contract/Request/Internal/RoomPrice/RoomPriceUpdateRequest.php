<?php

declare(strict_types=1);

namespace App\Contract\Request\Internal\RoomPrice;

use App\Helper\Request\RequestBodyInterface;
use App\Helper\Request\ValidatableRequest;

class RoomPriceUpdateRequest extends RoomPriceCreateRequest implements RequestBodyInterface, ValidatableRequest
{
}
