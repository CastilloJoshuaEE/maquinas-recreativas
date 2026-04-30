<?php
/**
 * Bootstrap/redis.php
 *
 * Se incluye desde bootstrap/app.php ANTES de session_start().
 * Intenta configurar sesiones en Redis; si no está disponible,
 * PHP usa su handler nativo (archivos) y el sistema sigue funcionando.
 */

use maquinas_recreativas\Infrastructure\Cache\CacheFactory;
use maquinas_recreativas\Infrastructure\Cache\RedisSessionHandler;

// Crear instancia de caché (Redis o NullCache)
$cache = CacheFactory::create();

// Intentar registrar el handler de sesión en Redis
if ($cache->isAvailable() && extension_loaded('redis')) {
    try {
        $redisConfig = require __DIR__ . '/../Config/redis.php';

        $redis = new \Redis();
        $redis->connect($redisConfig['host'], $redisConfig['port'], 2.0);

        if (!empty($redisConfig['password'])) {
            $redis->auth($redisConfig['password']);
        }
        if (isset($redisConfig['database'])) {
            $redis->select((int)$redisConfig['database']);
        }

        $sessionHandler = new RedisSessionHandler(
            $redis,
            $redisConfig['cache_ttl']['default'] ?? 3600,
            $redisConfig['session_prefix'] ?? 'session:'
        );

        session_set_save_handler($sessionHandler, true);
        error_log("[Bootstrap/redis] Sesiones configuradas en Redis.");
    } catch (\Throwable $e) {
        error_log("[Bootstrap/redis] No se pudo configurar sesión en Redis: " . $e->getMessage() . ". Usando sesiones nativas.");
    }
}

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Exponer instancia globalmente para que los repositorios puedan inyectarla
// (si tu contenedor de dependencias no lo hace automáticamente)
if (!isset($container)) {
    $GLOBALS['_cacheInstance'] = $cache;
}

return $cache;
