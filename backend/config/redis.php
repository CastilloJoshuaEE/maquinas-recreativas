<?php
return [
    'host' => EnvManager::get('REDIS_HOST', 'redis'),
    'port' => (int) EnvManager::get('REDIS_PORT', 6379),
    'password' => EnvManager::get('REDIS_PASSWORD', null),
    'database' => (int) EnvManager::get('REDIS_DATABASE', 0),
    'prefix' => 'maquinas:',
    'session_prefix' => 'session:',
    'cache_ttl' => [
        'short' => 300,      // 5 minutos
        'medium' => 3600,    // 1 hora
        'long' => 86400,     // 24 horas
        'default' => 3600,
    ],
    'rate_limits' => [
        'login' => ['limit' => 5, 'window' => 300],
        'register' => ['limit' => 3, 'window' => 300],
        'api' => ['limit' => 60, 'window' => 60],
    ],
];