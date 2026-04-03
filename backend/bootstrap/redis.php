<?php
use maquinas_recreativas\Infrastructure\Cache\RedisCache;
use maquinas_recreativas\Infrastructure\Cache\RedisSessionHandler;

// Configurar sesiones con Redis
$redisConfig = require __DIR__ . '/../Config/redis.php';
$redis = new Redis();
$redis->connect($redisConfig['host'], $redisConfig['port']);

if ($redisConfig['password']) {
    $redis->auth($redisConfig['password']);
}
$redis->select($redisConfig['database']);

// Registrar handler de sesión
$sessionHandler = new RedisSessionHandler($redis, $redisConfig['cache_ttl']['default'], $redisConfig['session_prefix']);
session_set_save_handler($sessionHandler, true);

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Registrar el cache en el contenedor de dependencias
Dependencies::register(RedisCache::class, function() use ($redisConfig) {
    return new RedisCache($redisConfig['host'], $redisConfig['port'], $redisConfig['prefix']);
});