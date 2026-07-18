<?php
/**
 * scripts/insertar-usuarios-iniciales.php
 * Script para insertar usuarios iniciales en el sistema
 */

// =============================================
// DEFINIR CONSTANTES DE ENCRIPTACION ANTES DE TODO
// =============================================
$constantsPath = __DIR__ . '/../Config/constants.php';
if (file_exists($constantsPath)) {
    require_once $constantsPath;
}
// Cargar autoload de Composer
require_once __DIR__ . '/../vendor/autoload.php';

// =============================================
// CONFIGURACION MANUAL DE BASE DE DATOS
// =============================================

$_ENV['DB_HOST'] = '127.0.0.1';
$_ENV['DB_PORT'] = '3306';
$_ENV['DB_NAME'] = 'bd_recrea_sys';
$_ENV['DB_USER'] = 'recrea_user';
$_ENV['DB_PASS'] = 'recrea_pass123';

$_SERVER['DB_HOST'] = '127.0.0.1';
$_SERVER['DB_PORT'] = '3306';
$_SERVER['DB_NAME'] = 'bd_recrea_sys';
$_SERVER['DB_USER'] = 'recrea_user';
$_SERVER['DB_PASS'] = 'recrea_pass123';

echo "Configuracion de base de datos:\n";
echo "  Host: " . ($_ENV['DB_HOST'] ?? 'No definido') . "\n";
echo "  Puerto: " . ($_ENV['DB_PORT'] ?? 'No definido') . "\n";
echo "  Base: " . ($_ENV['DB_NAME'] ?? 'No definido') . "\n";
echo "  Usuario: " . ($_ENV['DB_USER'] ?? 'No definido') . "\n";
echo "  Contrasena: " . (isset($_ENV['DB_PASS']) ? '*****' : 'No definido') . "\n\n";

use maquinas_recreativas\Infrastructure\Database\Database;
use maquinas_recreativas\Scripts\Inserter;

echo "=== Insertando usuarios iniciales ===\n";

try {
    $db = new Database();
    $conn = $db->getConnection();
    echo "Conexion a base de datos exitosa\n\n";
} catch (Exception $e) {
    echo "Error de conexion: " . $e->getMessage() . "\n";
    echo "Verifica que:\n";
    echo "  1. El servicio MySQL este corriendo\n";
    echo "  2. La base de datos 'bd_recrea_sys' exista\n";
    echo "  3. El usuario 'recrea_user' tenga permisos\n";
    echo "  4. Las credenciales sean correctas\n";
    exit(1);
}

// =============================================
// USAR LA CLASE INSERTER PARA INSERTAR
// =============================================

$inserter = new Inserter($conn);
$inserter->insertarUsuariosIniciales();

echo "\n=== Proceso completado ===\n";