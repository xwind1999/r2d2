<?php

declare(strict_types=1);

namespace App\Exception\Resolver;

use App\Exception\ContextualException;
use Symfony\Component\Messenger\Exception\UnrecoverableExceptionInterface;

class UnprocessableManageableProductTypeException extends ContextualException implements UnrecoverableExceptionInterface
{
    protected const CODE = 1100003;
    protected const MESSAGE = 'Unprocessable manageable product type';
}
