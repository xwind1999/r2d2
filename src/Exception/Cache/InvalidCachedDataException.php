<?php

declare(strict_types=1);

namespace App\Exception\Cache;

class InvalidCachedDataException extends AbstractCacheErrorException
{
    protected const CODE = 1600002;
    protected const MESSAGE = 'Invalid cached data';
}
