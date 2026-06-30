<?php
/**
 * scripts/insertar-usuarios-iniciales.php
 * Script para insertar usuarios iniciales en el sistema
 */

require_once __DIR__ . '/../bootstrap.php';

use maquinas_recreativas\Infrastructure\Database\Database;
use maquinas_recreativas\Infrastructure\Security\CifradoHelper;
use maquinas_recreativas\Infrastructure\Security\BcryptPasswordHasher;

echo "=== Insertando usuarios iniciales ===\n";

$db = new Database();
$conn = $db->getConnection();
$hasher = new BcryptPasswordHasher();

$usuarios = [
    [
        'id' => '11111111-1111-1111-1111-111111111111',
        'nombre' => 'Admin',
        'apellido' => 'Sistema',
        'ci' => '1234567890',
        'email' => 'admin@recreasys.com',
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
        'email' => 'tecnico@recreasys.com',
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
        'email' => 'logistica@recreasys.com',
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
        'email' => 'contabilidad@recreasys.com',
        'usuario_asignado' => 'contabilidad',
        'contrasena' => 'Contabilidad123!',
        'tipo' => 'Contabilidad',
        'estado' => 'Activo'
    ]
];

foreach ($usuarios as $usuario) {
    try {
        // Verificar si ya existe
        $checkStmt = $conn->prepare("SELECT COUNT(*) as total FROM usuario WHERE ID_Usuario = ?");
        $checkStmt->execute([$usuario['id']]);
        $row = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row['total'] > 0) {
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

        // Si es técnico, insertar en tabla Tecnico
        if ($usuario['tipo'] === 'Tecnico' && isset($usuario['especialidad'])) {
            $sqlTecnico = "INSERT INTO Tecnico (ID_Tecnico, Especialidad, Cantidad_Actividades) VALUES (?, ?, 0)";
            $stmtTecnico = $conn->prepare($sqlTecnico);
            $stmtTecnico->execute([$usuario['id'], $usuario['especialidad']]);
            echo "    -> Especialidad: {$usuario['especialidad']}\n";
        }

        // Si es logistica, insertar en tabla Logistica
        if ($usuario['tipo'] === 'Logistica') {
            $sqlLogistica = "INSERT INTO Logistica (ID_Logistica) VALUES (?)";
            $stmtLogistica = $conn->prepare($sqlLogistica);
            $stmtLogistica->execute([$usuario['id']]);
            echo "    -> Registrado como logistica\n";
        }

    } catch (Exception $e) {
        echo "  Error al insertar {$usuario['usuario_asignado']}: " . $e->getMessage() . "\n";
    }
}

echo "=== Proceso completado ===\n";