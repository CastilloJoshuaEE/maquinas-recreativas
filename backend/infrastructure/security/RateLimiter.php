<?php
/**
 * backend/infrastructure/security/RateLimiter.php
 */

namespace maquinas_recreativas\Infrastructure\Security;

use maquinas_recreativas\Infrastructure\Cache\RedisCache;

class RateLimiter
{
    private static ?self $instance = null;

    private $limits = [];
    private string $storageFile;
    private ?RedisCache $cache = null;

    private function __construct()
    {
        // Inicialización del almacenamiento en archivo
        $this->storageFile = __DIR__ . '/../../storage/rate_limits.json';
        $dir = dirname($this->storageFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        if (file_exists($this->storageFile)) {
            $this->limits = json_decode(file_get_contents($this->storageFile), true) ?: [];
        }

        $this->cleanOldLimits();

        // Inicialización de Redis si existe en el contenedor
        global $container;
        $this->cache = $container[RedisCache::class] ?? null;
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Verifica si se excedió el límite de requests
     */
    public function check(string $key, int $maxRequests = 60, int $timeWindow = 60): bool
    {
        if ($this->cache) {
            // Uso de Redis si está disponible
            $rateLimitKey = "rate_limit:{$key}";
            return $this->cache->checkRateLimit($rateLimitKey, $maxRequests, $timeWindow);
        }

        // Fallback: almacenamiento en archivo JSON
        $now = time();
        $windowKey = floor($now / $timeWindow);
        $storageKey = $key . '_' . $windowKey;

        if (!isset($this->limits[$storageKey])) {
            $this->limits[$storageKey] = [
                'count' => 1,
                'expires' => ($windowKey + 1) * $timeWindow
            ];
        } else {
            $this->limits[$storageKey]['count']++;
        }

        $this->save();
        return $this->limits[$storageKey]['count'] <= $maxRequests;
    }

    /**
     * Retorna la cantidad de requests restantes
     */
    public function getRemaining(string $key, int $maxRequests = 60, int $timeWindow = 60): int
    {
        if ($this->cache) {
            $rateLimitKey = "rate_limit:{$key}";
            return $this->cache->getRemaining($rateLimitKey, $maxRequests, $timeWindow);
        }

        $now = time();
        $windowKey = floor($now / $timeWindow);
        $storageKey = $key . '_' . $windowKey;
        $used = $this->limits[$storageKey]['count'] ?? 0;
        return max(0, $maxRequests - $used);
    }

    /**
     * Reinicia la clave específica
     */
    public function resetKey(string $key): void
    {
        if ($this->cache) {
            $rateLimitKey = "rate_limit:{$key}";
            $this->cache->delete($rateLimitKey . ':count');
            $this->cache->delete($rateLimitKey . ':window');
            return;
        }

        foreach (array_keys($this->limits) as $storageKey) {
            if (strpos($storageKey, $key . '_') === 0) {
                unset($this->limits[$storageKey]);
            }
        }
        $this->save();
    }

    /**
     * Reinicia todos los límites
     */
    public function resetAll(): void
    {
        if ($this->cache) {
            $this->cache->flush();
            return;
        }

        $this->limits = [];
        $this->save();
    }

    /**
     * Elimina límites caducados del archivo
     */
    private function cleanOldLimits(): void
    {
        $now = time();
        foreach ($this->limits as $key => $data) {
            if ($data['expires'] < $now) {
                unset($this->limits[$key]);
            }
        }
    }

    /**
     * Guarda el estado en archivo JSON
     */
    private function save(): void
    {
        file_put_contents($this->storageFile, json_encode($this->limits));
    }
}