<?php
/**
 * Infrastructure/Cache/RedisSessionHandler.php
 *
 * Maneja sesiones en Redis. Si Redis no está disponible el sistema
 * utiliza el handler nativo de PHP (archivos).
 */
namespace maquinas_recreativas\Infrastructure\Cache;

class RedisSessionHandler implements \SessionHandlerInterface
{
    private \Redis $redis;
    private int    $lifetime;
    private string $prefix;

    public function __construct(\Redis $redis, int $lifetime = 3600, string $prefix = 'session:')
    {
        $this->redis    = $redis;
        $this->lifetime = $lifetime;
        $this->prefix   = $prefix;
    }

    public function open($savePath, $name): bool  { return true; }
    public function close(): bool                  { return true; }

    public function read($id): string
    {
        try {
            $data = $this->redis->get($this->prefix . $id);
            return $data ?: '';
        } catch (\Throwable $e) {
            return '';
        }
    }

    public function write($id, $data): bool
    {
        try {
            return (bool) $this->redis->setex($this->prefix . $id, $this->lifetime, $data);
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function destroy($id): bool
    {
        try {
            $this->redis->del($this->prefix . $id);
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function gc($max_lifetime): int|false { return 1; }
}
