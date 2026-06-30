<?php
/**
 * config/env.php
 *
 * Carga y gestiona las variables de entorno desde el archivo .env
 * 
 * @package maquinas_recreativas\Config
 * @author Tu Equipo
 * @version 1.0.0
 */

class EnvManager
{
    private static array $variables = [];
    private static bool $loaded = false;

    public static function load(string $envPath = null): void
    {
        if (self::$loaded) {
            return;
        }

        if ($envPath === null) {
            $envPath = dirname(__DIR__) . '/.env';
        }

        /**
         *  NUEVO COMPORTAMIENTO:
         * - Si existe .env → lo carga (modo local)
         * - Si NO existe → no rompe (modo cloud: Render, Docker, etc)
         */

        if (!file_exists($envPath)) {

            //  Solo advertencia, nunca crash
            error_log("EnvManager: .env no encontrado en {$envPath}, usando variables del entorno del sistema");

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

            // sincronizar con entorno real
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }

        self::$loaded = true;
    }

    public static function get(string $key, $default = null)
    {
        if (!self::$loaded) {
            self::load();
        }

        /**
         *  NUEVA PRIORIDAD CORRECTA:
         * 1. Variables del sistema (Render / Docker / Apache env)
         * 2. getenv()
         * 3. .env cargado local
         * 4. $_ENV
         * 5. default
         */

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

        // fusionar también variables del sistema
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
            return self::get('DB_NAME_TEST', self::get('DB_NAME'));
        }

        return self::get('DB_NAME', 'bd_recrea_sys');
    }
}


// Definir constantes útiles para acceso rápido
if (!defined('APP_ENV')) {
    define('APP_ENV', EnvManager::getEnvironment());
}

if (!defined('APP_DEBUG')) {
    define('APP_DEBUG', EnvManager::get('APP_DEBUG', EnvManager::isDevelopment()));
}

if (!defined('APP_URL')) {
    define('APP_URL', EnvManager::get('APP_URL', 'http://localhost:8000'));
}

define('DB_HOST', EnvManager::get('DB_HOST'));
define('DB_USER', EnvManager::get('DB_USER'));
define('DB_PASS', EnvManager::get('DB_PASS'));
define('DB_NAME', EnvManager::getDatabaseName());