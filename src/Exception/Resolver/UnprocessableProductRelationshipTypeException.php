<?php

declare(strict_types=1);

namespace App\Exception\Resolver;

use App\Exception\ContextualException;

class UnprocessableProductRelationshipTypeException extends ContextualException
{
    protected const CODE = 1100002;
    protected const MESSAGE = 'Unprocessable product relationship type';
}
