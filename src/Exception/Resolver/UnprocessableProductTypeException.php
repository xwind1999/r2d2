<?php

declare(strict_types=1);

namespace App\Exception\Resolver;

use App\Exception\ContextualException;

class UnprocessableProductTypeException extends ContextualException
{
    protected const CODE = 1100001;
    protected const MESSAGE = 'Unprocessable product type';
}
