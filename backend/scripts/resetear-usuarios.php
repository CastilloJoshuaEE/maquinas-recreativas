<?php
/**
 * Script para resetear usuarios (eliminar y volver a insertar)
 * 
 * Ejecutar: php backend/scripts/resetear-usuarios.php
 */
// Cargar el EnvManager
require_once __DIR__ . '/../config/env.php';

// Cargar variables de entorno
EnvManager::load();

// =============================================
// DETECTAR ENTORNO
// =============================================
$isTest = EnvManager::isTesting();

// =============================================
// SELECCIONAR BASE DE DATOS
// =============================================
$dbName = EnvManager::getDatabaseName();

if ($isTest) {
    echo "✓ Modo TEST: usando base de datos {$dbName}\n";
}

if (!$dbName) {
    die(" No se ha definido DB_NAME en el .env\n");
}



require_once __DIR__ . '/../Infrastructure/Security/CifradoHelper.php';
require_once __DIR__ . '/../Infrastructure/Database/Database.php';
require_once __DIR__ . '/Inserter.php';

use maquinas_recreativas\Infrastructure\Database\Database;

echo "  ATENCIÓN: Este script ELIMINARÁ todos los usuarios existentes!\n";
echo "   (Excepto aquellos que tengan datos relacionados en otras tablas)\n\n";

$confirm = readline("¿Estás seguro de continuar? (escribe 'ELIMINAR' para confirmar): ");

if ($confirm !== 'ELIMINAR') {
    echo " Operación cancelada.\n";
    exit(0);
}

$database = new Database();
$connection = $database->getConnection();

// Eliminar lock file
$lockFile = __DIR__ . '/../config/.usuarios_iniciales.lock';
if (file_exists($lockFile)) {
    unlink($lockFile);
    echo " Lock file eliminado\n";
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
        echo "     {$table}: error - " . $connection->error . "\n";
    }
}

echo "\n Usuarios eliminados. Ahora ejecuta el script de inserción:\n";
echo "   php backend/scripts/insertar-usuarios-iniciales.php\n";