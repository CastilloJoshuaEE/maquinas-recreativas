<?php
/**
 * Config/redis.php
 * 
 * Configuración de Redis
 * En producción, las variables vienen del entorno (Render)
 */

return [
    'host' => EnvManager::get('REDIS_HOST', 'localhost'),
    'port' => (int) EnvManager::get('REDIS_PORT', 6379),
    'password' => EnvManager::get('REDIS_PASSWORD', ''),
    'database' => (int) EnvManager::get('REDIS_DATABASE', 0),
    'prefix' => EnvManager::get('REDIS_PREFIX', 'recreasys:'),
    'session_prefix' => EnvManager::get('REDIS_SESSION_PREFIX', 'session:'),
    'cache_ttl' => [
        'default' => (int) EnvManager::get('REDIS_CACHE_TTL', 1800),
        'short' => (int) EnvManager::get('REDIS_CACHE_TTL_SHORT', 300),
        'long' => (int) EnvManager::get('REDIS_CACHE_TTL_LONG', 7200),
    ],
];