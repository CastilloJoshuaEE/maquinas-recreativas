<?php
/**
 * Bootstrap para pruebas unitarias
 * 
 * @package maquinas_recreativas\Tests
 * @version 1.0
 */

// Cargar autoloader de Composer
$autoloadPath = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($autoloadPath)) {
    die("Error: Ejecuta 'composer install' primero.\n");
}
require_once $autoloadPath;

// =============================================
// CONFIGURAR ENTORNO DE PRUEBAS
// =============================================
// Cargar variables de entorno para pruebas
$envFile = __DIR__ . '/../.env.testing';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value, " \"'");
            putenv("$key=$value");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
}

if (!defined('TEST_ENVIRONMENT')) {
    define('TEST_ENVIRONMENT', true);
}
if (!defined('APP_ENV')) {
    define('APP_ENV', 'testing');
}
if (!defined('APP_DEBUG')) {
    define('APP_DEBUG', true);
}

// Configurar zona horaria
date_default_timezone_set('America/Guayaquil');

// Configurar manejo de errores para pruebas
error_reporting(E_ALL);
ini_set('display_errors', '1');

// =============================================
// CARGAR VARIABLES DE ENTORNO DESDE .env
// =============================================
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value, " \"'");
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
}

// =============================================
// CARGAR CONSTANTES DE CONFIGURACIÓN
// =============================================
// Cargar constants.php si existe
$constantsPath = __DIR__ . '/../Config/constants.php';
if (file_exists($constantsPath)) {
    require_once $constantsPath;
}

// Definir constantes de encriptación si no están definidas
if (!defined('ENCRYPT_METHOD')) {
    define('ENCRYPT_METHOD', getenv('ENCRYPT_METHOD') ?: 'AES-256-CBC');
}
if (!defined('SECRET_KEY')) {
    define('SECRET_KEY', getenv('SECRET_KEY') ?: 'clave_super_segura_cambiar_en_produccion_2024');
}
if (!defined('SECRET_IV')) {
    define('SECRET_IV', getenv('SECRET_IV') ?: 'vector_inicial_16');
}

// =============================================
// DEFINIR CONSTANTES PARA PRUEBAS
// =============================================
if (!defined('DB_DRIVER')) define('DB_DRIVER', getenv('DB_DRIVER') ?: 'mysql');
if (!defined('DB_HOST')) define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
if (!defined('DB_PORT')) define('DB_PORT', getenv('DB_PORT') ?: '3306');
if (!defined('DB_USER')) define('DB_USER', getenv('DB_USER') ?: 'recrea_user');
if (!defined('DB_PASS')) define('DB_PASS', getenv('DB_PASS') ?: 'recrea_pass123');
if (!defined('DB_NAME_TEST')) define('DB_NAME_TEST', getenv('DB_NAME_TEST') ?: 'test_bd_recrea_sys');

// Variables de entorno para pruebas
putenv('APP_ENV=testing');
putenv('DB_NAME_TEST=' . DB_NAME_TEST);

// =============================================
// CREAR BASE DE DATOS DE PRUEBA
// =============================================
try {
    $driver = DB_DRIVER;
    $host = DB_HOST;
    $port = DB_PORT;
    $dbname = DB_NAME_TEST;
    $user = DB_USER;
    $pass = DB_PASS;

    if ($driver === 'pgsql') {
        $dsn = "pgsql:host={$host};port={$port};dbname=postgres";
    } else {
        $dsn = "mysql:host={$host};port={$port};charset=utf8mb4";
    }

    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    if ($driver === 'pgsql') {
        $pdo->exec("CREATE DATABASE IF NOT EXISTS {$dbname}");
    } else {
        $pdo->exec("CREATE DATABASE IF NOT EXISTS {$dbname}");
    }
    
    $pdo = null;
} catch (Exception $e) {
    echo "Error creando base de datos de prueba: " . $e->getMessage() . "\n";
}

// =============================================
// CARGAR CLASES DE PRUEBA
// =============================================
require_once __DIR__ . '/TestDatabase.php';

// =============================================
// FUNCIONES DE AYUDA PARA PRUEBAS
// =============================================

/**
 * Limpia la base de datos de prueba entre pruebas
 */
function cleanTestDatabase(): void
{
    try {
        $testDb = \maquinas_recreativas\Tests\TestDatabase::getInstance();
        $testDb->cleanDatabase();
    } catch (Exception $e) {
        echo "Error limpiando base de datos: " . $e->getMessage() . "\n";
    }
}

/**
 * Reinicia la base de datos de prueba
 */
function resetTestDatabase(): void
{
    try {
        $testDb = \maquinas_recreativas\Tests\TestDatabase::getInstance();
        $testDb->cleanDatabase();
    } catch (Exception $e) {
        echo "Error reiniciando base de datos: " . $e->getMessage() . "\n";
    }
}

/**
 * Obtiene la instancia de TestDatabase
 */
function getTestDatabase(): \maquinas_recreativas\Tests\TestDatabase
{
    return \maquinas_recreativas\Tests\TestDatabase::getInstance();
}

echo " Bootstrap de pruebas cargado correctamente\n";
echo "   DB_DRIVER: " . DB_DRIVER . "\n";
echo "   DB_NAME_TEST: " . DB_NAME_TEST . "\n";
echo "   APP_ENV: " . APP_ENV . "\n";