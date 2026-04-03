<?php
namespace maquinas_recreativas\Infrastructure\Cache;

use Redis;
use SessionHandlerInterface;

class RedisSessionHandler implements SessionHandlerInterface
{
    private Redis $redis;
    private int $lifetime;
    private string $prefix;

    public function __construct(Redis $redis, int $lifetime = 3600, string $prefix = 'session:')
    {
        $this->redis = $redis;
        $this->lifetime = $lifetime;
        $this->prefix = $prefix;
    }

    public function open($savePath, $name): bool
    {
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read($id): string
    {
        $key = $this->prefix . $id;
        $data = $this->redis->get($key);
        return $data ?: '';
    }

    public function write($id, $data): bool
    {
        $key = $this->prefix . $id;
        return $this->redis->setex($key, $this->lifetime, $data);
    }

    public function destroy($id): bool
    {
        $key = $this->prefix . $id;
        return $this->redis->del($key) > 0;
    }

    public function gc($max_lifetime): int|false
    {
        return true;
    }
}