<?php

declare(strict_types=1);

namespace App\Contract\Request\Product;

use App\Helper\Request\RequestBodyInterface;
use App\Helper\Request\ValidatableRequest;

class ProductUpdateRequest extends ProductCreateRequest implements RequestBodyInterface, ValidatableRequest
{
}
