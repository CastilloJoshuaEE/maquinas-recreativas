<?php
/**
 * config/env.php
 *
 * Carga y gestiona las variables de entorno desde el archivo .env
 * 
 * @package maquinas_recreativas\Config
 */

class EnvManager
{
    private static array $variables = [];
    private static bool $loaded = false;
    private static string $envFileLoaded = '';

    public static function load(string $envPath = null): void
    {
        if (self::$loaded) {
            return;
        }

        // ============================================================
        // IMPORTANTE: Primero leer APP_ENV desde variables ya definidas
        // ============================================================
        $appEnv = getenv('APP_ENV') ?: $_ENV['APP_ENV'] ?? $_SERVER['APP_ENV'] ?? null;
        
        // Si APP_ENV es 'testing', forzarlo en todas partes ANTES de cargar cualquier archivo
        if ($appEnv === 'testing') {
            putenv('APP_ENV=testing');
            $_ENV['APP_ENV'] = 'testing';
            $_SERVER['APP_ENV'] = 'testing';
            
            // También forzar variables de base de datos de pruebas
            $dbNameTest = getenv('DB_NAME_TEST') ?: $_ENV['DB_NAME_TEST'] ?? $_SERVER['DB_NAME_TEST'] ?? null;
            if ($dbNameTest) {
                putenv('DB_NAME=' . $dbNameTest);
                putenv('DB_NAME_TEST=' . $dbNameTest);
                $_ENV['DB_NAME'] = $dbNameTest;
                $_ENV['DB_NAME_TEST'] = $dbNameTest;
                $_SERVER['DB_NAME'] = $dbNameTest;
                $_SERVER['DB_NAME_TEST'] = $dbNameTest;
            }
            
            error_log("EnvManager: APP_ENV forzado a 'testing' desde variables de entorno");
        }

        // Ahora determinar qué archivo cargar
        if ($envPath === null) {
            $basePath = dirname(__DIR__);
            
            // Verificar nuevamente APP_ENV (puede haber cambiado)
            //            $currentAppEnv = getenv('APP_ENV') ?: $_ENV['APP_ENV'] ?? 'testing'
            $currentAppEnv = getenv('APP_ENV') ?: $_ENV['APP_ENV'] ?? 'development';
            
            error_log("EnvManager: APP_ENV actual = '{$currentAppEnv}'");
            
            // Si es testing, buscar .env.testing
            if ($currentAppEnv === 'testing') {
                $testingEnv = $basePath . '/.env.testing';
                if (file_exists($testingEnv)) {
                    $envPath = $testingEnv;
                    self::$envFileLoaded = $envPath;
                    error_log("EnvManager: Cargando .env.testing desde: {$envPath}");
                } else {
                    error_log("EnvManager: .env.testing no encontrado en {$testingEnv}, buscando .env");
                }
            }
            
            // Si no se encontró .env.testing o no es testing, usar .env
            if ($envPath === null) {
                $envPath = $basePath . '/.env';
                if (file_exists($envPath)) {
                    self::$envFileLoaded = $envPath;
                    error_log("EnvManager: Cargando .env desde: {$envPath}");
                } else {
                    error_log("EnvManager: .env no encontrado en {$envPath}, usando variables del entorno del sistema");
                    self::$loaded = true;
                    return;
                }
            }
        }

        if (!file_exists($envPath)) {
            error_log("EnvManager: Archivo no encontrado: {$envPath}");
            self::$loaded = true;
            return;
        }

        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '' || strpos($line, '#') === 0) {
                continue;
            }

            $parts = explode('=', $line, 2);

            if (count($parts) !== 2) {
                continue;
            }

            $key = trim($parts[0]);
            $value = trim($parts[1]);
            $value = trim($value, "\"'");

            self::$variables[$key] = $value;

            // Sincronizar con entorno real (sin sobrescribir si ya existe)
            if (getenv($key) === false) {
                putenv("$key=$value");
            }
            if (!isset($_ENV[$key])) {
                $_ENV[$key] = $value;
            }
            if (!isset($_SERVER[$key])) {
                $_SERVER[$key] = $value;
            }
        }

        // Asegurar que APP_ENV no sea sobrescrito por el archivo
        if ($appEnv === 'testing') {
            putenv('APP_ENV=testing');
            $_ENV['APP_ENV'] = 'testing';
            $_SERVER['APP_ENV'] = 'testing';
        }

        self::$loaded = true;
        error_log("EnvManager: Cargadas " . count(self::$variables) . " variables de entorno desde " . basename($envPath));
    }
    public static function get(string $key, $default = null)
    {
        if (!self::$loaded) {
            self::load();
        }

        // Prioridad: $_SERVER > getenv() > variables cargadas > $_ENV > default
        $value = $_SERVER[$key] ?? null;
        if ($value !== null) {
            return $value;
        }

        $value = getenv($key);
        if ($value !== false) {
            return $value;
        }

        if (array_key_exists($key, self::$variables)) {
            return self::$variables[$key];
        }

        if (array_key_exists($key, $_ENV)) {
            return $_ENV[$key];
        }

        return $default;
    }

    public static function all(): array
    {
        if (!self::$loaded) {
            self::load();
        }

        return array_merge($_ENV, self::$variables);
    }

    public static function has(string $key): bool
    {
        if (!self::$loaded) {
            self::load();
        }

        return isset($_SERVER[$key])
            || getenv($key) !== false
            || array_key_exists($key, self::$variables)
            || array_key_exists($key, $_ENV);
    }

    public static function getEnvironment(): string
    {
        return self::get('APP_ENV', 'local');
    }

    public static function isDevelopment(): bool
    {
        return in_array(self::getEnvironment(), ['development', 'local']);
    }

    public static function isTesting(): bool
    {
        return self::getEnvironment() === 'testing'
            || (defined('TEST_ENVIRONMENT') && TEST_ENVIRONMENT === true);
    }

    public static function isProduction(): bool
    {
        return self::getEnvironment() === 'production';
    }

    public static function getDatabaseName(): string
    {
        if (self::isTesting()) {
            return self::get('DB_NAME', self::get('DB_NAME_TEST', 'test_bd_recrea_sys'));
        }

        return self::get('DB_NAME', 'bd_recrea_sys');
    }

    /**
     * Obtiene la ruta del archivo .env cargado
     */
    public static function getLoadedFile(): string
    {
        return self::$envFileLoaded;
    }
}

// Definir constantes útiles para acceso rápido
if (!defined('APP_ENV')) {
    // Obtener APP_ENV de variables de entorno
    $appEnv = getenv('APP_ENV') ?: $_ENV['APP_ENV'] ?? 'local';
    define('APP_ENV', $appEnv);
}

if (!defined('APP_DEBUG')) {
    define('APP_DEBUG', EnvManager::get('APP_DEBUG', EnvManager::isDevelopment()));
}

if (!defined('APP_URL')) {
    define('APP_URL', EnvManager::get('APP_URL', 'http://localhost:8000'));
}

// Definir constantes de base de datos
define('DB_HOST', EnvManager::get('DB_HOST', 'localhost'));
define('DB_USER', EnvManager::get('DB_USER', 'root'));
define('DB_PASS', EnvManager::get('DB_PASS', ''));
define('DB_NAME', EnvManager::getDatabaseName());