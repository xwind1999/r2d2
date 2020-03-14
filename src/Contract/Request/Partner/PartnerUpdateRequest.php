<?php

declare(strict_types=1);

namespace App\Contract\Request\Partner;

use App\Helper\Request\RequestBodyInterface;
use App\Helper\Request\ValidatableRequest;

class PartnerUpdateRequest extends PartnerCreateRequest implements RequestBodyInterface, ValidatableRequest
{
}
