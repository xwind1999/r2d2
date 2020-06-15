<?php

declare(strict_types=1);

namespace App\Exception\Manager\Component;

use App\Exception\ContextualException;
use Symfony\Component\Messenger\Exception\UnrecoverableExceptionInterface;

class OutdatedComponentException extends ContextualException implements UnrecoverableExceptionInterface
{
    protected const CODE = 1500005;
    protected const MESSAGE = 'Outdated component received';
}
