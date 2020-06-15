<?php

declare(strict_types=1);

namespace App\Exception\Manager\Experience;

use App\Exception\ContextualException;
use Symfony\Component\Messenger\Exception\UnrecoverableExceptionInterface;

class OutdatedExperienceException extends ContextualException implements UnrecoverableExceptionInterface
{
    protected const CODE = 1500006;
    protected const MESSAGE = 'Outdated experience received';
}
