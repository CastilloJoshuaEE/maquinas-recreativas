<?php
/**
 * maquinas_recreativas/backend/bootstrap/app.php - Bootstrap Application
 * 
 * Inicializa todos los componentes del sistema en el orden correcto:
 * 1. Configuración de errores y entorno
 * 2. Variables de entorno
 * 3. Autoloader
 * 4. Configuración de sesión
 * 5. Headers de seguridad
 * 6. Contenedor de dependencias
 * 
 * @package maquinas_recreativas\Bootstrap
 * @author Tu Equipo
 * @version 1.0
 */

// =============================================
// 1. CONFIGURACIÓN DE ERRORES
// =============================================
ini_set('display_errors', '0');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// Buffer de salida para manejar headers
ob_start();

// Eliminar headers sensibles
header_remove('X-Powered-By');

// =============================================
// 2. VARIABLES DE ENTORNO
// =============================================
require_once __DIR__ . '/env.php';

// =============================================
// 3. AUTOLOADER DE COMPOSER
// =============================================
$autoloadPath = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($autoloadPath)) {
    die("Error: Ejecuta 'composer install' primero.");
}
require_once $autoloadPath;

// =============================================
// 4. CONSTANTES GLOBALES
// =============================================
require_once __DIR__ . '/../config/constants.php';

// =============================================
// 5. CONFIGURACIÓN DE SESIÓN
// =============================================
require_once __DIR__ . '/session.php';

// =============================================
// 6. HEADERS DE SEGURIDAD
// =============================================
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/rate_limit.php';
// =============================================
// 7. DETECTAR ENTORNO DE PRUEBAS
// =============================================
$isTestEnvironment = (
    (isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'PHPUnit') !== false) ||
    (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/test') !== false) ||
    (isset($_GET['test']))
);

if ($isTestEnvironment && !defined('TEST_ENVIRONMENT')) {
    define('TEST_ENVIRONMENT', true);
}

// =============================================
// 8. INICIALIZAR CONTENEDOR DE DEPENDENCIAS
// =============================================
require_once __DIR__ . '/../config/dependencies.php';
// Cargar Redis y configurar sesiones

require_once __DIR__ . '/redis.php';
