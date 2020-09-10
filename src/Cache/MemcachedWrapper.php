<?php

declare(strict_types=1);

namespace App\Cache;

use App\Exception\Cache\ResourceNotCachedException;
use Symfony\Component\Cache\Adapter\MemcachedAdapter;

/**
 * @codeCoverageIgnore
 */
class MemcachedWrapper
{
    private const DEFAULT_LIFETIME = 600;

    private string $dsn;

    private ?\Memcached $client = null;

    public function __construct(string $dsn)
    {
        $this->dsn = $dsn;
    }

    private function getClient(): \Memcached
    {
        if (null === $this->client) {
            $this->client = MemcachedAdapter::createConnection($this->dsn);
        }

        return $this->client;
    }

    /**
     * @return mixed
     */
    public function get(string $key)
    {
        $data = $this->getClient()->get($key);

        if (!$data && \Memcached::RES_SUCCESS !== $this->getClient()->getResultCode()) {
            throw new ResourceNotCachedException();
        }

        return $data;
    }

    /**
     * @param mixed $value
     */
    public function set(string $key, $value, int $duration = self::DEFAULT_LIFETIME): bool
    {
        return $this->getClient()->set($key, $value, $duration);
    }

    public function delete(string $key): void
    {
        $this->getClient()->delete($key);
    }

    public function deleteMulti(array $keys): void
    {
        $this->getClient()->deleteMulti($keys);
    }
}
