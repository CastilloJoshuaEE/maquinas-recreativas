<?php
/**
 * Infrastructure/Cache/CacheInterface.php
 */
namespace maquinas_recreativas\Infrastructure\Cache;

interface CacheInterface
{
    public function get(string $key, $default = null);
    public function set(string $key, $value, int $ttl = null): bool;
    public function delete(string $key): bool;
    public function exists(string $key): bool;
    public function increment(string $key, int $amount = 1): int;
    public function decrement(string $key, int $amount = 1): int;
    public function flush(): bool;
    public function remember(string $key, callable $callback, int $ttl = null);
    public function checkRateLimit(string $key, int $limit, int $window): bool;
    public function getRemaining(string $key, int $limit, int $window): int;
    public function isAvailable(): bool;
}
