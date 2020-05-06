<?php

declare(strict_types=1);

namespace App\Exception\Resolver;

use App\Exception\ContextualException;
use Symfony\Component\Messenger\Exception\UnrecoverableExceptionInterface;

class UnprocessableProductRelationshipTypeException extends ContextualException implements UnrecoverableExceptionInterface
{
    protected const CODE = 1100002;
    protected const MESSAGE = 'Unprocessable product relationship type';
}
