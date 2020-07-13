<?php

declare(strict_types=1);

namespace App\Exception\Manager\Component;

use App\Exception\ContextualException;
use Symfony\Component\Messenger\Exception\UnrecoverableExceptionInterface;

class ComponentIsNotManageableException extends ContextualException implements UnrecoverableExceptionInterface
{
    protected const CODE = 1500008;
    protected const MESSAGE = 'Component is not manageable';
}
