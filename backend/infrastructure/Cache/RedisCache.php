<?php
/**
 * Infrastructure/Cache/RedisCache.php
 *
 * Implementación Redis del caché. Si Redis no está disponible o falla,
 * cada método captura la excepción y devuelve un valor seguro, de modo
 * que el sistema continúa operando sin caché.
 */
namespace maquinas_recreativas\Infrastructure\Cache;

class RedisCache implements CacheInterface
{
    private ?\Redis $redis = null;
    private string  $prefix;
    private int     $defaultTtl;
    private bool    $connected = false;

    public function __construct(
        string $host       = 'redis',
        int    $port       = 6379,
        string $prefix     = 'maquinas:',
        int    $defaultTtl = 3600
    ) {
        $this->prefix     = $prefix;
        $this->defaultTtl = $defaultTtl;
        $this->connect($host, $port);
    }

    private function connect(string $host, int $port): void
    {
        try {
            $r = new \Redis();
            $ok = @$r->connect($host, $port, 2.0); // timeout 2 s
            if ($ok) {
                $r->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_PHP);
                $this->redis     = $r;
                $this->connected = true;
            }
        } catch (\Throwable $e) {
            error_log("[RedisCache] No se pudo conectar a Redis ({$host}:{$port}): " . $e->getMessage());
        }
    }

    private function k(string $key): string
    {
        return $this->prefix . $key;
    }

    public function isAvailable(): bool
    {
        return $this->connected && $this->redis !== null;
    }

    // -------------------------------------------------------------------------
    // CacheInterface
    // -------------------------------------------------------------------------

    public function get(string $key, $default = null)
    {
        if (!$this->isAvailable()) return $default;
        try {
            $v = $this->redis->get($this->k($key));
            return $v !== false ? $v : $default;
        } catch (\Throwable $e) {
            error_log("[RedisCache] get error: " . $e->getMessage());
            return $default;
        }
    }

    public function set(string $key, $value, int $ttl = null): bool
    {
        if (!$this->isAvailable()) return false;
        try {
            return (bool) $this->redis->setex($this->k($key), $ttl ?? $this->defaultTtl, $value);
        } catch (\Throwable $e) {
            error_log("[RedisCache] set error: " . $e->getMessage());
            return false;
        }
    }

    public function delete(string $key): bool
    {
        if (!$this->isAvailable()) return false;
        try {
            return $this->redis->del($this->k($key)) > 0;
        } catch (\Throwable $e) {
            error_log("[RedisCache] delete error: " . $e->getMessage());
            return false;
        }
    }

    public function exists(string $key): bool
    {
        if (!$this->isAvailable()) return false;
        try {
            return $this->redis->exists($this->k($key)) > 0;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function increment(string $key, int $amount = 1): int
    {
        if (!$this->isAvailable()) return 0;
        try {
            return (int) $this->redis->incrBy($this->k($key), $amount);
        } catch (\Throwable $e) {
            return 0;
        }
    }

    public function decrement(string $key, int $amount = 1): int
    {
        if (!$this->isAvailable()) return 0;
        try {
            return (int) $this->redis->decrBy($this->k($key), $amount);
        } catch (\Throwable $e) {
            return 0;
        }
    }

    public function flush(): bool
    {
        if (!$this->isAvailable()) return false;
        try {
            $keys = $this->redis->keys($this->prefix . '*');
            if (!empty($keys)) {
                $this->redis->del($keys);
            }
            return true;
        } catch (\Throwable $e) {
            error_log("[RedisCache] flush error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Si el valor está en caché lo devuelve; si no, ejecuta $callback,
     * almacena el resultado y lo devuelve.
     */
    public function remember(string $key, callable $callback, int $ttl = null)
    {
        if ($this->isAvailable()) {
            try {
                $cached = $this->get($key);
                if ($cached !== null) {
                    return $cached;
                }
                $value = $callback();
                $this->set($key, $value, $ttl);
                return $value;
            } catch (\Throwable $e) {
                error_log("[RedisCache] remember error: " . $e->getMessage());
            }
        }
        // Fallback: ejecutar directamente sin caché
        return $callback();
    }

    // -------------------------------------------------------------------------
    // Rate limiting
    // -------------------------------------------------------------------------

    public function checkRateLimit(string $key, int $limit, int $window): bool
    {
        if (!$this->isAvailable()) return true; // sin Redis, se permite
        try {
            $countKey  = $this->k($key . ':count');
            $windowKey = $this->k($key . ':window');

            $currentWindow = $this->redis->get($windowKey);
            $currentCount  = (int)($this->redis->get($countKey) ?? 0);

            if (!$currentWindow || (int)$currentWindow < time()) {
                $this->redis->setex($windowKey, $window, (string)time());
                $this->redis->setex($countKey, $window, '1');
                return true;
            }
            if ($currentCount >= $limit) {
                return false;
            }
            $this->redis->incr($countKey);
            return true;
        } catch (\Throwable $e) {
            return true;
        }
    }

    public function getRemaining(string $key, int $limit, int $window): int
    {
        if (!$this->isAvailable()) return $limit;
        try {
            $count = (int)($this->redis->get($this->k($key . ':count')) ?? 0);
            return max(0, $limit - $count);
        } catch (\Throwable $e) {
            return $limit;
        }
    }

    /**
     * Elimina múltiples claves por patrón (útil para invalidar listados).
     * Usa KEYS — no usar en producción con millones de claves.
     */
    public function deleteByPattern(string $pattern): int
    {
try {
            $fullPattern = $this->prefix . $pattern;
            $keys = $this->redis->keys($fullPattern);
            
            if (empty($keys)) {
                return 0;
            }
            
            return $this->redis->del($keys);
        } catch (\Exception $e) {
            error_log("[RedisCache] Error en deleteByPattern: " . $e->getMessage());
            return 0;
        }
    }
}
