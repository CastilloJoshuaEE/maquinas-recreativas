<?php
/**
 * Script para resetear usuarios (eliminar y volver a insertar)
 * 
 * Ejecutar: php backend/scripts/resetear-usuarios.php
 */

// Cargar configuración igual que el script anterior
$envPath = __DIR__ . '/../.env';
if (!file_exists($envPath)) {
    die("❌ Archivo .env no encontrado\n");
}
$env = parse_ini_file($envPath);

define('DB_HOST', $env['DB_HOST']);
define('DB_USER', $env['DB_USER']);
define('DB_PASS', $env['DB_PASS']);
define('DB_NAME', $env['DB_NAME']);

define('ENCRYPT_METHOD', 'AES-256-CBC');
define('SECRET_KEY', $env['SECRET_KEY'] ?? 'clave_super_segura_cambiar_en_produccion_2024');
define('SECRET_IV', $env['SECRET_IV'] ?? 'vector_inicial_16');

require_once __DIR__ . '/../Infrastructure/Security/CifradoHelper.php';
require_once __DIR__ . '/../Infrastructure/Database/Database.php';
require_once __DIR__ . '/Inserter.php';

use maquinas_recreativas\Infrastructure\Database\Database;

echo "⚠️  ATENCIÓN: Este script ELIMINARÁ todos los usuarios existentes!\n";
echo "   (Excepto aquellos que tengan datos relacionados en otras tablas)\n\n";

$confirm = readline("¿Estás seguro de continuar? (escribe 'ELIMINAR' para confirmar): ");

if ($confirm !== 'ELIMINAR') {
    echo "❌ Operación cancelada.\n";
    exit(0);
}

$database = new Database();
$connection = $database->getConnection();

// Eliminar lock file
$lockFile = __DIR__ . '/../config/.usuarios_iniciales.lock';
if (file_exists($lockFile)) {
    unlink($lockFile);
    echo "✅ Lock file eliminado\n";
}

// Eliminar usuarios (en orden inverso por restricciones de clave foránea)
echo "\n🗑️  Eliminando usuarios existentes...\n";

$tables = [
    'comentario',
    'notificaciones',
    'NotificacionMaquinaRecreativa',
    'componente_usuario',
    'inicio_sesion',
    'historial_actividades',
    'Tecnico',
    'Logistica',
    'reporte',
    'usuario'
];

foreach ($tables as $table) {
    $sql = "DELETE FROM {$table} WHERE 1=1";
    if ($connection->query($sql)) {
        echo "   ✓ {$table}: registros eliminados\n";
    } else {
        echo "   ⚠️  {$table}: error - " . $connection->error . "\n";
    }
}

echo "\n✅ Usuarios eliminados. Ahora ejecuta el script de inserción:\n";
echo "   php backend/scripts/insertar-usuarios-iniciales.php\n";