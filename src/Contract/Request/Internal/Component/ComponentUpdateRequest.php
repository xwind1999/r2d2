<?php

declare(strict_types=1);

namespace App\Contract\Request\Internal\Component;

use App\Helper\Request\RequestBodyInterface;
use App\Helper\Request\ValidatableRequest;

class ComponentUpdateRequest extends ComponentCreateRequest implements RequestBodyInterface, ValidatableRequest
{
}
