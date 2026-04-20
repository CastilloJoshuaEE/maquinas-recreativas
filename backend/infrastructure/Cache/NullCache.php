<?php
/**
 * Infrastructure/Cache/NullCache.php
 *
 * Implementación nula del caché. Se usa cuando Redis no está disponible
 * (p.ej. hosting compartido como InfinityFree). El sistema funciona igual,
 * solo sin caching.
 */
namespace maquinas_recreativas\Infrastructure\Cache;

class NullCache implements CacheInterface
{
        private array $cache = [];

    public function get(string $key, $default = null)
    {
        return $default;
    }

    public function set(string $key, $value, int $ttl = null): bool
    {
        return true;
    }

    public function delete(string $key): bool
    {
        return true;
    }

    public function exists(string $key): bool
    {
        return false;
    }

    public function increment(string $key, int $amount = 1): int
    {
        return 0;
    }

    public function decrement(string $key, int $amount = 1): int
    {
        return 0;
    }

    public function flush(): bool
    {
        return true;
    }

    /**
     * Sin caché: siempre ejecuta el callback directamente.
     */
    public function remember(string $key, callable $callback, int $ttl = null)
    {
        return $callback();
    }

    public function checkRateLimit(string $key, int $limit, int $window): bool
    {
        return true; // Sin Redis no hay rate limiting distribuido
    }

    public function getRemaining(string $key, int $limit, int $window): int
    {
        return $limit;
    }

    public function isAvailable(): bool
    {
        return false;
    }
    public function deleteByPattern(string $pattern): int
    {
        $count = 0;
        $pattern = str_replace('*', '.*', $pattern);
        
        foreach (array_keys($this->cache) as $key) {
            if (preg_match('/^' . $pattern . '$/', $key)) {
                unset($this->cache[$key]);
                $count++;
            }
        }
        
        return $count;
    }
}
