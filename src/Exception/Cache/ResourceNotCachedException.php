<?php

declare(strict_types=1);

namespace App\Exception\Cache;

class ResourceNotCachedException extends AbstractCacheErrorException
{
    protected const CODE = 1600001;
    protected const MESSAGE = 'Resource not cached';
}
