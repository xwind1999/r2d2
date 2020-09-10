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

    private string $environmentName;

    private ?\Memcached $client = null;

    public function __construct(string $dsn, string $environmentName)
    {
        $this->dsn = $dsn;
        $this->environmentName = $environmentName;
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
        $data = $this->getClient()->get($this->prefixKey($key));

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
        return $this->getClient()->set($this->prefixKey($key), $value, $duration);
    }

    public function delete(string $key): void
    {
        $this->getClient()->delete($this->prefixKey($key));
    }

    public function deleteMulti(array $keys): void
    {
        foreach ($keys as &$key) {
            $key = $this->prefixKey($key);
        }

        $this->getClient()->deleteMulti($keys);
    }

    private function prefixKey(string $key): string
    {
        return $this->environmentName.$key;
    }
}
