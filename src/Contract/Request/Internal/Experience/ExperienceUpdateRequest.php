<?php

declare(strict_types=1);

namespace App\Contract\Request\Internal\Experience;

use App\Helper\Request\RequestBodyInterface;
use App\Helper\Request\ValidatableRequest;

class ExperienceUpdateRequest extends ExperienceCreateRequest implements RequestBodyInterface, ValidatableRequest
{
}
