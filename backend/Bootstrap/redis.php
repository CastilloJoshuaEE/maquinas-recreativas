<?php
/**
 * Bootstrap/redis.php
 *
 * Intenta configurar Redis para caché, pero no falla si no está disponible.
 * El sistema funcionará sin Redis usando NullCache.
 */

use maquinas_recreativas\Infrastructure\Cache\CacheFactory;
use maquinas_recreativas\Infrastructure\Cache\RedisSessionHandler;

// =============================================
// CREAR INSTANCIA DE CACHÉ (Redis o NullCache)
// =============================================
$cache = CacheFactory::create();

// =============================================
// INTENTAR CONFIGURAR SESIÓN EN REDIS
// =============================================
// Solo si Redis está disponible y la extensión está cargada
if ($cache->isAvailable() && extension_loaded('redis')) {
    try {
        $redisConfig = require __DIR__ . '/../Config/redis.php';

        $redis = new \Redis();
        $redis->connect($redisConfig['host'], $redisConfig['port'], 1.0); // Timeout reducido a 1s

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
        error_log("[Bootstrap/redis] Sesiones configuradas en Redis correctamente.");
    } catch (\Throwable $e) {
        // Silencioso - no romper la aplicación si Redis falla
        error_log("[Bootstrap/redis] Redis no disponible: " . $e->getMessage() . ". Usando sesiones nativas.");
    }
}

// =============================================
// INICIAR SESIÓN SI NO ESTÁ INICIADA
// =============================================
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

// Si falla, forzar el handler de archivos
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.save_handler', 'files');
    session_start();
}

// Exponer instancia globalmente
if (!isset($container)) {
    $GLOBALS['_cacheInstance'] = $cache;
}

return $cache;