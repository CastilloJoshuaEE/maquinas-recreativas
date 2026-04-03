<?php
namespace maquinas_recreativas\Infrastructure\Cache;

use Redis;
use RedisException;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;

class RedisCache implements CacheInterface
{
    private Redis $redis;
    private string $prefix;
    private int $defaultTtl;

    public function __construct(string $host = 'redis', int $port = 6379, string $prefix = 'maquinas:', int $defaultTtl = 3600)
    {
        $this->redis = new Redis();
        $this->prefix = $prefix;
        $this->defaultTtl = $defaultTtl;

        try {
            $this->redis->connect($host, $port);
            $this->redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);
        } catch (RedisException $e) {
            throw new DomainException('No se pudo conectar a Redis: ' . $e->getMessage());
        }
    }

    private function getPrefixedKey(string $key): string
    {
        return $this->prefix . $key;
    }

    public function get(string $key, $default = null)
    {
        $value = $this->redis->get($this->getPrefixedKey($key));
        return $value !== false ? $value : $default;
    }

    public function set(string $key, $value, int $ttl = null): bool
    {
        $ttl = $ttl ?? $this->defaultTtl;
        return $this->redis->setex($this->getPrefixedKey($key), $ttl, $value);
    }

    public function delete(string $key): bool
    {
        return $this->redis->del($this->getPrefixedKey($key)) > 0;
    }

    public function exists(string $key): bool
    {
        return $this->redis->exists($this->getPrefixedKey($key)) > 0;
    }

    public function increment(string $key, int $amount = 1): int
    {
        return $this->redis->incrBy($this->getPrefixedKey($key), $amount);
    }

    public function decrement(string $key, int $amount = 1): int
    {
        return $this->redis->decrBy($this->getPrefixedKey($key), $amount);
    }

    public function flush(): bool
    {
        $keys = $this->redis->keys($this->prefix . '*');
        if (!empty($keys)) {
            return $this->redis->del($keys) > 0;
        }
        return true;
    }

    public function remember(string $key, callable $callback, int $ttl = null)
    {
        $value = $this->get($key);
        
        if ($value !== null) {
            return $value;
        }
        
        $value = $callback();
        $this->set($key, $value, $ttl);
        
        return $value;
    }

    /**
     * Métodos específicos para Rate Limiting
     */
    public function getRemaining(string $key, int $limit, int $window): int
    {
        $current = $this->redis->get($this->getPrefixedKey($key . ':count')) ?? 0;
        return max(0, $limit - $current);
    }

    public function checkRateLimit(string $key, int $limit, int $window): bool
    {
        $countKey = $this->getPrefixedKey($key . ':count');
        $windowKey = $this->getPrefixedKey($key . ':window');
        
        $currentWindow = $this->redis->get($windowKey);
        $currentCount = $this->redis->get($countKey) ?? 0;
        
        if (!$currentWindow || $currentWindow < time()) {
            $this->redis->setex($windowKey, $window, time());
            $this->redis->setex($countKey, $window, 1);
            return true;
        }
        
        if ($currentCount >= $limit) {
            return false;
        }
        
        $this->redis->incr($countKey);
        return true;
    }
}