<?php
/**
 * Bootstrap/redis.php
 * 
 * Configuración de Redis para caché. Si no está disponible, usa NullCache.
 * Las sesiones SIEMPRE usan archivos, NO Redis.
 */

use maquinas_recreativas\Infrastructure\Cache\CacheFactory;

// =============================================
// CREAR INSTANCIA DE CACHÉ (Redis o NullCache)
// =============================================
$cache = CacheFactory::create();

// =============================================
// VERIFICAR DISPONIBILIDAD DE REDIS
// =============================================
if ($cache->isAvailable()) {
    error_log("[Bootstrap/redis] Redis está disponible para caché.");
} else {
    error_log("[Bootstrap/redis] Redis no disponible, usando NullCache.");
}

// NOTA: Las sesiones NO usan Redis, usan el handler de archivos configurado en session.php

// Exponer instancia globalmente
if (!isset($container)) {
    $GLOBALS['_cacheInstance'] = $cache;
}

return $cache;