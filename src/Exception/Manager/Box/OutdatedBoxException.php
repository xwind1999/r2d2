<?php

declare(strict_types=1);

namespace App\Exception\Manager\Box;

use App\Exception\ContextualException;
use Symfony\Component\Messenger\Exception\UnrecoverableExceptionInterface;

class OutdatedBoxException extends ContextualException implements UnrecoverableExceptionInterface
{
    protected const CODE = 1500004;
    protected const MESSAGE = 'Outdated box received';
}
