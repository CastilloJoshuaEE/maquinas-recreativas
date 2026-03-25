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

/**
 * Clase EnvManager
 * 
 * Gestiona la carga y acceso a las variables de entorno del sistema.
 */
class EnvManager
{
    /**
     * @var array Almacena las variables de entorno cargadas
     */
    private static array $variables = [];
    
    /**
     * @var bool Indica si las variables ya fueron cargadas
     */
    private static bool $loaded = false;
    
    /**
     * Carga las variables de entorno desde el archivo .env
     * 
     * @param string $envPath Ruta al archivo .env (opcional)
     * @return void
     * @throws RuntimeException Si el archivo .env no existe
     */
    public static function load(string $envPath = null): void
    {
        if (self::$loaded) {
            return;
        }
        
        if ($envPath === null) {
            $envPath = dirname(__DIR__) . '/.env';
        }
        
        if (!file_exists($envPath)) {
            // En desarrollo, permitir continuar con valores por defecto
            if (getenv('APP_ENV') !== 'production') {
                error_log("Advertencia: Archivo .env no encontrado en: {$envPath}");
                self::$loaded = true;
                return;
            }
            throw new RuntimeException("Archivo .env no encontrado en: {$envPath}");
        }
        
        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            // Ignorar comentarios
            if (strpos($line, '#') === 0) {
                continue;
            }
            
            // Buscar asignación de variable
            $parts = explode('=', $line, 2);
            if (count($parts) === 2) {
                $key = trim($parts[0]);
                $value = trim($parts[1]);
                
                // Remover comillas si existen
                $value = trim($value, '"\'');
                
                self::$variables[$key] = $value;
                
                // También establecer como variable de entorno para funciones getenv()
                putenv("{$key}={$value}");
                $_ENV[$key] = $value;
            }
        }
        
        self::$loaded = true;
    }
    
    /**
     * Obtiene una variable de entorno
     * 
     * @param string $key Nombre de la variable
     * @param mixed $default Valor por defecto si no existe
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        if (!self::$loaded) {
            self::load();
        }
        
        // Prioridad: getenv() -> array interno -> $_ENV -> $default
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
    
    /**
     * Obtiene todas las variables de entorno cargadas
     * 
     * @return array
     */
    public static function all(): array
    {
        if (!self::$loaded) {
            self::load();
        }
        return self::$variables;
    }
    
    /**
     * Verifica si una variable de entorno existe
     * 
     * @param string $key
     * @return bool
     */
    public static function has(string $key): bool
    {
        if (!self::$loaded) {
            self::load();
        }
        
        return array_key_exists($key, self::$variables) || 
               getenv($key) !== false ||
               array_key_exists($key, $_ENV);
    }
    
    /**
     * Obtiene el entorno actual (local, testing, production)
     * 
     * @return string
     */
    public static function getEnvironment(): string
    {
        return self::get('APP_ENV', 'local');
    }
    
    /**
     * Verifica si estamos en entorno de desarrollo
     * 
     * @return bool
     */
    public static function isDevelopment(): bool
    {
        return self::getEnvironment() === 'development' || self::getEnvironment() === 'local';
    }
    
    /**
     * Verifica si estamos en entorno de pruebas
     * 
     * @return bool
     */
    public static function isTesting(): bool
    {
        return self::getEnvironment() === 'testing' || 
               (defined('TEST_ENVIRONMENT') && TEST_ENVIRONMENT === true);
    }
    
    /**
     * Verifica si estamos en entorno de producción
     * 
     * @return bool
     */
    public static function isProduction(): bool
    {
        return self::getEnvironment() === 'production';
    }
    
    /**
     * Obtiene el nombre de la base de datos según el entorno
     * 
     * @return string
     */
    public static function getDatabaseName(): string
    {
        if (self::isTesting()) {
            return self::get('DB_NAME_TEST', self::get('DB_NAME', 'bd_recrea_sys_test'));
        }
        return self::get('DB_NAME', 'bd_recrea_sys');
    }
}

// Cargar variables de entorno inmediatamente
EnvManager::load();

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

if (!defined('DB_HOST')) {
    define('DB_HOST', EnvManager::get('DB_HOST', 'localhost'));
}

if (!defined('DB_USER')) {
    define('DB_USER', EnvManager::get('DB_USER', 'root'));
}

if (!defined('DB_PASS')) {
    define('DB_PASS', EnvManager::get('DB_PASS', ''));
}

if (!defined('DB_NAME')) {
    define('DB_NAME', EnvManager::getDatabaseName());
}