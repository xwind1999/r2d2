<?php

declare(strict_types=1);

namespace App\Cache;

use App\Contract\Response\QuickData\GetRangeResponse;
use App\Exception\Cache\InvalidCachedDataException;
use App\Exception\Cache\ResourceNotCachedException;

class QuickDataCache
{
    private const BOX_DATE_CACHE_DURATION = 60 * 60;
    private const KEY_SEPARATOR = '.';
    private const BOX_DATE_PREFIX = 'box'.self::KEY_SEPARATOR;

    private MemcachedWrapper $memcachedWrapper;

    public function __construct(MemcachedWrapper $memcachedWrapper)
    {
        $this->memcachedWrapper = $memcachedWrapper;
    }

    /**
     * @throws ResourceNotCachedException
     */
    public function getBoxDate(string $boxGoldenId, string $startDate): GetRangeResponse
    {
        $data = $this->memcachedWrapper->get($this->boxDateKey($boxGoldenId, $startDate));

        if (!$data instanceof GetRangeResponse) {
            throw new InvalidCachedDataException();
        }

        return $data;
    }

    public function setBoxDate(string $boxGoldenId, string $startDate, GetRangeResponse $data): bool
    {
        return $this->memcachedWrapper->set(
            $this->boxDateKey($boxGoldenId, $startDate),
            $data,
            self::BOX_DATE_CACHE_DURATION
        );
    }

    public function boxDateKey(string $boxGoldenId, string $startDate): string
    {
        return static::BOX_DATE_PREFIX.$boxGoldenId.self::KEY_SEPARATOR.$startDate;
    }

    public function massInvalidate(array $keys): void
    {
        $this->memcachedWrapper->deleteMulti($keys);
    }
}
