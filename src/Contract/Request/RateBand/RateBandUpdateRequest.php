<?php

declare(strict_types=1);

namespace App\Contract\Request\RateBand;

use App\Helper\Request\RequestBodyInterface;
use App\Helper\Request\ValidatableRequest;

class RateBandUpdateRequest extends RateBandCreateRequest implements RequestBodyInterface, ValidatableRequest
{
}
