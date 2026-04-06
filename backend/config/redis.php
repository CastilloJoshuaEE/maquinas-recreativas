<?php
/**
 * Config/redis.php
 *
 * Configuración de Redis. Se leen desde constantes definidas en config/app.php
 * o variables de entorno. Si no existen, se usan valores por defecto seguros.
 */
return [
    'host'           => defined('REDIS_HOST')     ? REDIS_HOST     : 'redis',
    'port'           => defined('REDIS_PORT')     ? (int)REDIS_PORT : 6379,
    'password'       => defined('REDIS_PASSWORD') ? REDIS_PASSWORD  : null,
    'database'       => defined('REDIS_DATABASE') ? (int)REDIS_DATABASE : 0,
    'prefix'         => defined('REDIS_PREFIX')   ? REDIS_PREFIX   : 'maquinas:',
    'session_prefix' => 'session:',

    // TTL por categoría (segundos)
    'cache_ttl' => [
        'muy_corto'  => 120,   // Notificaciones, Reportes, Comentarios (tiempo real/chat)
        'corto'      => 300,   // Historial, Recaudación, Admin estadísticas
        'medio'      => 600,   // Comercio, Componente, Distribución
        'largo'      => 1800,  // Máquina, Usuario, Técnico (datos estables)
        'default'    => 3600,
    ],

    // Rate limiting
    'rate_limits' => [
        'login'    => ['limit' => 5,  'window' => 300],
        'register' => ['limit' => 3,  'window' => 300],
        'api'      => ['limit' => 60, 'window' => 60],
    ],
];
