<?php
namespace maquinas_recreativas\Application\Queries;

use maquinas_recreativas\Infrastructure\Cache\RedisCache;

abstract class CacheableQueryHandler implements QueryHandler
{
    protected RedisCache $cache;
    protected int $cacheTtl = 3600;

    public function __construct(RedisCache $cache = null)
    {
        $this->cache = $cache ?? new RedisCache();
    }

    protected function remember(string $key, callable $callback, ?int $ttl = null): mixed
    {
        $ttl = $ttl ?? $this->cacheTtl;
        return $this->cache->remember($key, $callback, $ttl);
    }

    protected function forget(string $key): void
    {
        $this->cache->delete($key);
    }

    protected function forgetPattern(string $pattern): void
    {
        // Implementar si es necesario
    }
}