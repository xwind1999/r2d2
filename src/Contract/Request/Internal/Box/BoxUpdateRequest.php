<?php

declare(strict_types=1);

namespace App\Contract\Request\Internal\Box;

use App\Helper\Request\RequestBodyInterface;
use App\Helper\Request\ValidatableRequest;

class BoxUpdateRequest extends BoxCreateRequest implements RequestBodyInterface, ValidatableRequest
{
}
