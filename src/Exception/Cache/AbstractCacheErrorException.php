<?php

declare(strict_types=1);

namespace App\Exception\Cache;

use App\Exception\ContextualException;

abstract class AbstractCacheErrorException extends ContextualException
{
    protected const CODE = 1600000;
    protected const MESSAGE = 'Cache error';
}
