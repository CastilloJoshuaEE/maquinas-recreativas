<?php
/**
 * Script para insertar usuarios iniciales en la base de datos
 * 
 * Ejecutar: php backend/scripts/insertar-usuarios-iniciales.php
 */

// =============================================
// CARGAR CONFIGURACIÓN CON ENVMANAGER
// =============================================

// Cargar el EnvManager
require_once __DIR__ . '/../Config/env.php';

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



// =============================================
// INCLUIR DEPENDENCIAS
// =============================================
require_once __DIR__ . '/../Infrastructure/Security/CifradoHelper.php';
require_once __DIR__ . '/../Infrastructure/Database/Database.php';
require_once __DIR__ . '/Inserter.php';
require_once __DIR__ . '/../Config/constants.php';
require_once __DIR__ . '/../Config/env.php';

use maquinas_recreativas\Infrastructure\Database\Database;
use maquinas_recreativas\Infrastructure\Database\Inserter;

// =============================================
// CONEXIÓN A LA BASE DE DATOS
// =============================================
echo "✓ Conectando a la base de datos...\n";

$database = new Database();
$connection = $database->getConnection();

if (!$connection) {
    die(" Error de conexión a la base de datos\n");
}

echo "✓ Conexión establecida a: " . DB_NAME . "\n";

// =============================================
// VERIFICAR Y CREAR LOCK FILE
// =============================================
$lockFile = __DIR__ . '/../Config/.usuarios_iniciales.lock';

// Verificar si ya se ejecutó
if (file_exists($lockFile)) {
    echo "⚠ Los usuarios iniciales ya fueron insertados anteriormente.\n";
    echo "  Lock file: {$lockFile}\n";
    echo "  Si deseas reiniciar la inserción, elimina este archivo y vuelve a ejecutar el script.\n";
    
    $confirm = readline("¿Deseas forzar la inserción de todos modos? (s/N): ");
    if (strtolower($confirm) !== 's') {
        echo " Operación cancelada.\n";
        exit(0);
    }
    echo " Forzando inserción...\n";
}

// =============================================
// INSERTAR USUARIOS
// =============================================
echo "\n Insertando usuarios iniciales...\n";
echo str_repeat("-", 50) . "\n";

$inserter = new Inserter($connection);

try {
    $inserter->insertarUsuariosIniciales();
    
    // Crear lock file
    file_put_contents($lockFile, "Usuarios iniciales creados el " . date("Y-m-d H:i:s") . "\n");
    
    echo "\n Usuarios insertados correctamente!\n";
    echo " Lock file creado en: {$lockFile}\n";
    
    // Mostrar resumen
    echo "\n RESUMEN DE USUARIOS INSERTADOS:\n";
    echo str_repeat("-", 50) . "\n";
    
    $result = $connection->query("SELECT usuario_asignado, nombre, apellido, tipo, estado FROM usuario ORDER BY tipo, nombre");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            echo "• {$row['usuario_asignado']} | {$row['nombre']} {$row['apellido']} | {$row['tipo']} | {$row['estado']}\n";
        }
        $result->close();
    }
    
} catch (Exception $e) {
    echo "\n Error al insertar usuarios iniciales:\n";
    echo "   " . $e->getMessage() . "\n";
    exit(1);
}

// =============================================
// VERIFICACIÓN FINAL
// =============================================
echo "\n🔍 Verificando que los datos se pueden desencriptar correctamente...\n";
echo str_repeat("-", 50) . "\n";

$testUsers = ['admin1', 'euro', 'joshua', 'joel'];

foreach ($testUsers as $username) {
    $result = $connection->query("SELECT ID_Usuario, usuario_asignado, email, ci FROM usuario WHERE usuario_asignado = '{$username}'");
    if ($result && $row = $result->fetch_assoc()) {
        try {
            $emailDec = \maquinas_recreativas\Infrastructure\Security\CifradoHelper::desencriptar($row['email']);
            $ciDec = \maquinas_recreativas\Infrastructure\Security\CifradoHelper::desencriptar($row['ci']);
            
            if (!empty($emailDec) && !empty($ciDec)) {
                echo " {$username}: Email={$emailDec}, CI={$ciDec}\n";
            } else {
                echo "⚠ {$username}: Desencriptación parcial - Email=" . ($emailDec ?: 'VACÍO') . ", CI=" . ($ciDec ?: 'VACÍO') . "\n";
            }
        } catch (Exception $e) {
            echo " {$username}: Error al desencriptar - " . $e->getMessage() . "\n";
        }
        $result->close();
    }
}

echo "\n🎉 Proceso completado!\n";