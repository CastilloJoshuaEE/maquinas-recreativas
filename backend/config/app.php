<?php
/**
 * backend/Config/app.php
 *
 * Configuración general de la aplicación
 * 
 * @package maquinas_recreativas\Config
 * @author Tu Equipo
 * @version 1.0.0
 */

// =============================================
// CONFIGURACIÓN DE ZONA HORARIA
// =============================================

date_default_timezone_set(EnvManager::get('APP_TIMEZONE', 'America/Guayaquil'));

// =============================================
// CONFIGURACIÓN DE ERRORES
// =============================================

if (APP_DEBUG) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
}

// =============================================
// CONFIGURACIÓN DE MEMORIA Y EJECUCIÓN
// =============================================

ini_set('memory_limit', EnvManager::get('MEMORY_LIMIT', '256M'));
ini_set('max_execution_time', EnvManager::get('MAX_EXECUTION_TIME', 120));

// =============================================
// CONFIGURACIÓN DE ARCHIVOS
// =============================================

ini_set('upload_max_filesize', EnvManager::get('UPLOAD_MAX_FILESIZE', '10M'));
ini_set('post_max_size', EnvManager::get('POST_MAX_SIZE', '10M'));

// =============================================
// CONFIGURACIÓN DE LOGS
// =============================================

define('LOG_PATH', EnvManager::get('LOG_PATH', RUTA_STORAGE . 'logs/'));
define('LOG_LEVEL', EnvManager::get('LOG_LEVEL', APP_DEBUG ? 'debug' : 'error'));

// Crear directorio de logs si no existe
if (!is_dir(LOG_PATH)) {
    mkdir(LOG_PATH, 0777, true);
}

// =============================================
// CONFIGURACIÓN DE CACHE
// =============================================

define('CACHE_PATH', EnvManager::get('CACHE_PATH', RUTA_STORAGE . 'cache/'));
define('CACHE_ENABLED', EnvManager::get('CACHE_ENABLED', !APP_DEBUG));

if (!is_dir(CACHE_PATH) && CACHE_ENABLED) {
    mkdir(CACHE_PATH, 0777, true);
}

// =============================================
// CONFIGURACIÓN DE CORREO
// =============================================

define('MAIL_HOST', EnvManager::get('MAIL_HOST', 'smtp.gmail.com'));
define('MAIL_PORT', EnvManager::get('MAIL_PORT', 587));
define('MAIL_USERNAME', EnvManager::get('MAIL_USERNAME', ''));
define('MAIL_PASSWORD', EnvManager::get('MAIL_PASSWORD', ''));
define('MAIL_ENCRYPTION', EnvManager::get('MAIL_ENCRYPTION', 'tls'));
define('MAIL_FROM_ADDRESS', EnvManager::get('MAIL_FROM_ADDRESS', 'noreply@recreasys.com'));
define('MAIL_FROM_NAME', EnvManager::get('MAIL_FROM_NAME', 'Recrea Sys S.A.'));

// =============================================
// CONFIGURACIÓN DE API
// =============================================

define('API_VERSION', EnvManager::get('API_VERSION', 'v1'));
define('API_BASE_PATH', EnvManager::get('API_BASE_PATH', '/api/public'));

// =============================================
// CONFIGURACIÓN DE CORS (sobrescribe si es necesario)
// =============================================

$allowedOrigins = EnvManager::get('CORS_ALLOWED_ORIGINS', '');
if ($allowedOrigins) {
    $allowedOriginsArray = explode(',', $allowedOrigins);
} else {
    $allowedOriginsArray = [
        'http://localhost:4200',
        'http://localhost:8000',
        'http://127.0.0.1:4200',
        'http://127.0.0.1:8000',
    ];
}

define('CORS_ALLOWED_ORIGINS', $allowedOriginsArray);
define('CORS_ALLOWED_METHODS', EnvManager::get('CORS_ALLOWED_METHODS', 'GET, POST, PUT, PATCH, DELETE, OPTIONS'));
define('CORS_ALLOWED_HEADERS', EnvManager::get('CORS_ALLOWED_HEADERS', 'Content-Type, Authorization, X-Requested-With'));
define('CORS_MAX_AGE', EnvManager::get('CORS_MAX_AGE', 86400));

// =============================================
// CONFIGURACIÓN DE RATE LIMITING
// =============================================

define('RATE_LIMIT_ENABLED', EnvManager::get('RATE_LIMIT_ENABLED', !APP_DEBUG));
define('RATE_LIMIT_STORAGE', EnvManager::get('RATE_LIMIT_STORAGE', RUTA_RATE_LIMITS));

// =============================================
// CONFIGURACIÓN DE JWT (si se usa)
// =============================================

define('JWT_SECRET', EnvManager::get('JWT_SECRET', SECRET_KEY));
define('JWT_TTL', EnvManager::get('JWT_TTL', 3600)); // 1 hora

// =============================================
// CONFIGURACIÓN DE ENCRYPTACIÓN (si se sobreescribe)
// =============================================

if (EnvManager::has('ENCRYPT_METHOD')) {
    define('ENCRYPT_METHOD', EnvManager::get('ENCRYPT_METHOD'));
}

if (EnvManager::has('SECRET_KEY')) {
    define('SECRET_KEY', EnvManager::get('SECRET_KEY'));
}

if (EnvManager::has('SECRET_IV')) {
    define('SECRET_IV', EnvManager::get('SECRET_IV'));
}

// =============================================
// FUNCIÓN DE LOGGING SIMPLE
// =============================================

if (!function_exists('log_message')) {
    /**
     * Escribe un mensaje en el log del sistema
     * 
     * @param string $level Nivel de log (debug, info, warning, error)
     * @param string $message Mensaje a registrar
     * @param array $context Datos adicionales
     * @return void
     */
    function log_message(string $level, string $message, array $context = []): void
    {
        $levels = ['debug', 'info', 'warning', 'error'];
        $logLevels = [
            'debug' => 0,
            'info' => 1,
            'warning' => 2,
            'error' => 3,
        ];
        
        $currentLevel = $logLevels[LOG_LEVEL] ?? 0;
        $messageLevel = $logLevels[$level] ?? 0;
        
        if ($messageLevel < $currentLevel) {
            return;
        }
        
        $date = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' ' . json_encode($context, JSON_UNESCAPED_UNICODE) : '';
        
        $logEntry = "[{$date}] [{$level}] {$message}{$contextStr}\n";
        
        $logFile = LOG_PATH . date('Y-m-d') . '.log';
        file_put_contents($logFile, $logEntry, FILE_APPEND);
    }
}

// =============================================
// FUNCIÓN DE DEBUG (solo en desarrollo)
// =============================================

if (!function_exists('dd')) {
    /**
     * Dump and die - Muestra variables y detiene la ejecución
     * Solo disponible en modo debug
     * 
     * @param mixed ...$vars Variables a mostrar
     * @return void
     */
    function dd(...$vars): void
    {
        if (!APP_DEBUG) {
            return;
        }
        
        header('Content-Type: text/html; charset=utf-8');
        echo '<pre style="background:#f5f5f5; padding:20px; border:1px solid #ddd; margin:20px; font-family:monospace;">';
        foreach ($vars as $var) {
            var_dump($var);
            echo "\n\n";
        }
        echo '</pre>';
        die(1);
    }
}

if (!function_exists('dump')) {
    /**
     * Dump - Muestra variables sin detener la ejecución
     * Solo disponible en modo debug
     * 
     * @param mixed ...$vars Variables a mostrar
     * @return void
     */
    function dump(...$vars): void
    {
        if (!APP_DEBUG) {
            return;
        }
        
        foreach ($vars as $var) {
            echo '<pre style="background:#f5f5f5; padding:10px; border:1px solid #ddd; margin:10px; font-family:monospace;">';
            var_dump($var);
            echo '</pre>';
        }
    }
}

// =============================================
// VALIDACIÓN DE CONFIGURACIÓN
// =============================================

// Verificar que las constantes esenciales están definidas
$requiredConstants = [
    'APP_ENV',
    'APP_DEBUG',
    'DB_HOST',
    'DB_USER',
    'DB_NAME',
];

foreach ($requiredConstants as $constant) {
    if (!defined($constant)) {
        log_message('error', "Constante requerida no definida: {$constant}");
        if (APP_DEBUG) {
            throw new RuntimeException("Constante requerida no definida: {$constant}");
        }
    }
}

// Log de inicio de aplicación
log_message('info', 'Aplicación inicializada', [
    'environment' => APP_ENV,
    'debug' => APP_DEBUG,
    'php_version' => PHP_VERSION
]);