<?php
/**
 * scripts/insertar-usuarios-iniciales.php
 * Script para insertar usuarios iniciales en el sistema
 */

// =============================================
// DEFINIR CONSTANTES DE ENCRIPTACION ANTES DE TODO
// =============================================

if (!defined('SECRET_KEY')) {
    define('SECRET_KEY', 'clave_super_segura_cambiar_en_produccion_1234567890abcdef');
}

if (!defined('SECRET_IV')) {
    define('SECRET_IV', 'vector_inicial_16_abcdefghijk');
}

if (!defined('ENCRYPT_METHOD')) {
    define('ENCRYPT_METHOD', 'AES-256-CBC');
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
use maquinas_recreativas\Infrastructure\Security\CifradoHelper;
use maquinas_recreativas\Infrastructure\Security\BcryptPasswordHasher;

echo "=== Insertando usuarios iniciales ===\n";

try {
    $db = new Database();
    $conn = $db->getConnection();
    $hasher = new BcryptPasswordHasher();
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

$usuarios = [
    [
        'id' => '11111111-1111-1111-1111-111111111111',
        'nombre' => 'Admin',
        'apellido' => 'Sistema',
        'ci' => '1234567890',
        'email' => 'admin@gmail.com',
        'usuario_asignado' => 'admin',
        'contrasena' => 'Admin123!',
        'tipo' => 'Administrador',
        'estado' => 'Activo'
    ],
    [
        'id' => '22222222-2222-2222-2222-222222222222',
        'nombre' => 'Tecnico',
        'apellido' => 'Ensamblador',
        'ci' => '0987654321',
        'email' => 'tecnico@gmail.com',
        'usuario_asignado' => 'tecnico',
        'contrasena' => 'Tecnico123!',
        'tipo' => 'Tecnico',
        'estado' => 'Activo',
        'especialidad' => 'Ensamblador'
    ],
    [
        'id' => '33333333-3333-3333-3333-333333333333',
        'nombre' => 'Logistica',
        'apellido' => 'Distribucion',
        'ci' => '1122334455',
        'email' => 'logistica@gmail.com',
        'usuario_asignado' => 'logistica',
        'contrasena' => 'Logistica123!',
        'tipo' => 'Logistica',
        'estado' => 'Activo'
    ],
    [
        'id' => '44444444-4444-4444-4444-444444444444',
        'nombre' => 'Contabilidad',
        'apellido' => 'Finanzas',
        'ci' => '5544332211',
        'email' => 'contabilidad@gmail.com',
        'usuario_asignado' => 'contabilidad',
        'contrasena' => 'Contabilidad123!',
        'tipo' => 'Contabilidad',
        'estado' => 'Activo'
    ]
];

echo "Creando " . count($usuarios) . " usuarios...\n\n";

foreach ($usuarios as $usuario) {
    try {
        // Verificar si ya existe por ID
        $checkStmt = $conn->prepare("SELECT COUNT(*) as total FROM usuario WHERE ID_Usuario = ?");
        $checkStmt->execute([$usuario['id']]);
        $row = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row['total'] > 0) {
            echo "  Usuario {$usuario['usuario_asignado']} ya existe, saltando...\n";
            continue;
        }

        // Verificar si ya existe por usuario_asignado
        $checkStmt2 = $conn->prepare("SELECT COUNT(*) as total FROM usuario WHERE usuario_asignado = ?");
        $checkStmt2->execute([$usuario['usuario_asignado']]);
        $row2 = $checkStmt2->fetch(PDO::FETCH_ASSOC);
        
        if ($row2['total'] > 0) {
            echo "  Usuario {$usuario['usuario_asignado']} ya existe, saltando...\n";
            continue;
        }

        // Encriptar datos
        $ciEncriptada = CifradoHelper::encriptar($usuario['ci']);
        $emailEncriptado = CifradoHelper::encriptar($usuario['email']);
        $contrasenaHash = $hasher->hash($usuario['contrasena']);

        // Insertar usuario
        $sql = "INSERT INTO usuario (ID_Usuario, nombre, apellido, ci, email, usuario_asignado, contrasena, tipo, estado)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            $usuario['id'],
            $usuario['nombre'],
            $usuario['apellido'],
            $ciEncriptada,
            $emailEncriptado,
            $usuario['usuario_asignado'],
            $contrasenaHash,
            $usuario['tipo'],
            $usuario['estado']
        ]);

        echo "  Usuario {$usuario['usuario_asignado']} creado correctamente\n";

        // Si es tecnico, insertar en tabla Tecnico
        if ($usuario['tipo'] === 'Tecnico' && isset($usuario['especialidad'])) {
            // Verificar si ya existe en Tecnico
            $checkTecnico = $conn->prepare("SELECT COUNT(*) as total FROM Tecnico WHERE ID_Tecnico = ?");
            $checkTecnico->execute([$usuario['id']]);
            $rowTecnico = $checkTecnico->fetch(PDO::FETCH_ASSOC);
            
            if ($rowTecnico['total'] == 0) {
                $sqlTecnico = "INSERT INTO Tecnico (ID_Tecnico, Especialidad, Cantidad_Actividades) VALUES (?, ?, 0)";
                $stmtTecnico = $conn->prepare($sqlTecnico);
                $stmtTecnico->execute([$usuario['id'], $usuario['especialidad']]);
                echo "    Especialidad: {$usuario['especialidad']}\n";
            } else {
                echo "    Ya registrado como tecnico\n";
            }
        }

        // Si es logistica, insertar en tabla Logistica
        if ($usuario['tipo'] === 'Logistica') {
            // Verificar si ya existe en Logistica
            $checkLogistica = $conn->prepare("SELECT COUNT(*) as total FROM Logistica WHERE ID_Logistica = ?");
            $checkLogistica->execute([$usuario['id']]);
            $rowLogistica = $checkLogistica->fetch(PDO::FETCH_ASSOC);
            
            if ($rowLogistica['total'] == 0) {
                $sqlLogistica = "INSERT INTO Logistica (ID_Logistica) VALUES (?)";
                $stmtLogistica = $conn->prepare($sqlLogistica);
                $stmtLogistica->execute([$usuario['id']]);
                echo "    Registrado como logistica\n";
            } else {
                echo "    Ya registrado como logistica\n";
            }
        }

    } catch (Exception $e) {
        echo "  Error al insertar {$usuario['usuario_asignado']}: " . $e->getMessage() . "\n";
    }
}

echo "\n=== Proceso completado ===\n";