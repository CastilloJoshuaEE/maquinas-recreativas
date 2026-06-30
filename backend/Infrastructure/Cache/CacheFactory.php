<?php
/**
 * Infrastructure/Cache/CacheFactory.php
 *
 * Crea la instancia de caché apropiada. Intenta Redis; si no está disponible
 */
namespace maquinas_recreativas\Infrastructure\Cache;

class CacheFactory
{
    private static ?CacheInterface $instance = null;

    public static function create(): CacheInterface
    {
        if (self::$instance !== null) {
            return self::$instance;
        }

        // Si la extensión Redis no está instalada, usar NullCache directamente
        if (!extension_loaded('redis')) {
            error_log("[CacheFactory] Extensión redis no cargada. Usando NullCache.");
            self::$instance = new NullCache();
            return self::$instance;
        }

        try {
            $host   = defined('REDIS_HOST')     ? REDIS_HOST     : 'redis';
            $port   = defined('REDIS_PORT')     ? (int)REDIS_PORT : 6379;
            $prefix = defined('REDIS_PREFIX')   ? REDIS_PREFIX   : 'maquinas:';
            $ttl    = defined('REDIS_TTL')       ? (int)REDIS_TTL  : 3600;

            $cache = new RedisCache($host, $port, $prefix, $ttl);

            if ($cache->isAvailable()) {
                self::$instance = $cache;
            } else {
                error_log("[CacheFactory] Redis no disponible. Usando NullCache.");
                self::$instance = new NullCache();
            }
        } catch (\Throwable $e) {
            error_log("[CacheFactory] Error creando RedisCache: " . $e->getMessage() . ". Usando NullCache.");
            self::$instance = new NullCache();
        }

        return self::$instance;
    }

    /** Permite resetear la instancia (útil en tests). */
    public static function reset(): void
    {
        self::$instance = null;
    }
}
