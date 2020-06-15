<?php

declare(strict_types=1);

namespace App\Exception\Manager\Partner;

use App\Exception\ContextualException;
use Symfony\Component\Messenger\Exception\UnrecoverableExceptionInterface;

class OutdatedPartnerException extends ContextualException implements UnrecoverableExceptionInterface
{
    protected const CODE = 1500001;
    protected const MESSAGE = 'Outdated partner received';
}
