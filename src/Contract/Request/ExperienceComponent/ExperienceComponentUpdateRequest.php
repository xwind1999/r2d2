<?php

declare(strict_types=1);

namespace App\Contract\Request\ExperienceComponent;

use App\Helper\Request\RequestBodyInterface;
use App\Helper\Request\ValidatableRequest;

class ExperienceComponentUpdateRequest extends ExperienceComponentCreateRequest implements RequestBodyInterface, ValidatableRequest
{
}
