<?php
/**
 * Bootstrap para pruebas unitarias
 * 
 * @package maquinas_recreativas\Tests
 * @version 1.0
 */

// Configurar entorno de pruebas
if (!defined('TEST_ENVIRONMENT')) {
    define('TEST_ENVIRONMENT', true);
}
if (!defined('APP_ENV')) {
    define('APP_ENV', 'testing');
}
if (!defined('APP_DEBUG')) {
    define('APP_DEBUG', true);
}

// Cargar autoloader de Composer
$autoloadPath = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($autoloadPath)) {
    die("Error: Ejecuta 'composer install' primero.\n");
}
require_once $autoloadPath;

// =============================================
// DEFINIR CONSTANTES PARA PRUEBAS
// =============================================
if (!defined('DB_HOST')) define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
if (!defined('DB_USER')) define('DB_USER', getenv('DB_USER') ?: 'root');
if (!defined('DB_PASS')) define('DB_PASS', getenv('DB_PASS') ?: '');
if (!defined('DB_NAME')) define('DB_NAME', getenv('DB_NAME_TEST') ?: 'test_bd_recrea_sys');

// Cargar constantes de configuración
require_once __DIR__ . '/../Config/constants.php';

// Configurar zona horaria
date_default_timezone_set('America/Guayaquil');

// Configurar manejo de errores para pruebas
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Variables de entorno para pruebas
putenv('APP_ENV=testing');
putenv('DB_NAME_TEST=test_bd_recrea_sys');

// Cargar TestDatabase
require_once __DIR__ . '/TestDatabase.php';

// Helper para limpiar base de datos entre pruebas
function cleanTestDatabase(): void
{
    $testDb = \maquinas_recreativas\Tests\TestDatabase::getInstance();
    $testDb->cleanDatabase();
}
function resetTestDatabase(): void
{
    $testDb = \maquinas_recreativas\Tests\TestDatabase::getInstance();
    $testDb->cleanDatabase();
}