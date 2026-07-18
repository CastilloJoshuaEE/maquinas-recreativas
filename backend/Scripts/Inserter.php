<?php
/**
 * backend/Scripts/Inserter.php
 */

namespace maquinas_recreativas\Scripts;

use maquinas_recreativas\Infrastructure\Security\CifradoHelper;

class Inserter {
    private $connection;

    public function __construct($connection) {
        $this->connection = $connection;
    }

    public function insertarUsuariosIniciales() {
        $usuarios = [
            // Administrador:
            [
                'nombre' => 'Jean',
                'apellido' => 'Castro',
                'ci' => '1111111111',
                'email' => 'jean@admin.com',
                'contrasena' => '12345678',
                'tipo' => 'Administrador',
                'usuario_asignado' => 'admin1',
                'estado' => 'Activo'
            ],
            // Contabilidad:
            [
                'nombre' => 'Sebastián',
                'apellido' => 'Ramírez',
                'ci' => '0987765499',
                'email' => 'sebas@admin.com',
                'contrasena' => '12345678',
                'tipo' => 'Contabilidad',
                'usuario_asignado' => 'sebas',
                'estado' => 'Activo'
            ],
            // Logística:
            [
                'nombre' => 'Edú',
                'apellido' => 'Sabando',
                'ci' => '1316789914',
                'email' => 'esb@gmail.com',
                'contrasena' => '12345678',
                'tipo' => 'Logistica',
                'usuario_asignado' => 'esb',
                'estado' => 'Activo'
            ],
            // Técnicos:
            [
                'nombre' => 'Joshúa',
                'apellido' => 'Castillo',
                'ci' => '0987654321',
                'email' => 'joshua@gmail.com',
                'contrasena' => '12345678',
                'tipo' => 'Tecnico',
                'usuario_asignado' => 'joshua',
                'especialidad' => 'Ensamblador',
                'estado' => 'Activo'
            ],
            [
                'nombre' => 'Euro',
                'apellido' => 'Quiroz',
                'ci' => '0987667890',
                'email' => 'euro@gmail.com',
                'contrasena' => '12345678',
                'tipo' => 'Tecnico',
                'usuario_asignado' => 'euro',
                'especialidad' => 'Comprobador',
                'estado' => 'Activo'
            ],
            [
                'nombre' => 'Joel',
                'apellido' => 'Gabino',
                'ci' => '0980980987',
                'email' => 'joel@gmail.com',
                'contrasena' => '12345678',
                'tipo' => 'Tecnico',
                'usuario_asignado' => 'joel',
                'especialidad' => 'Mantenimiento',
                'estado' => 'Activo'
            ]
        ];

        foreach ($usuarios as $usuario) {
            $this->insertarUsuarioSiNoExiste($usuario);
        }
    }

    private function insertarUsuarioSiNoExiste($datosUsuario) {
        if (!$this->usuarioExiste($datosUsuario['email'], $datosUsuario['ci'])) {
            $this->insertarUsuario($datosUsuario);
            return true;
        }
        return false;
    }

    private function usuarioExiste($email, $ci) {
        $emailEnc = CifradoHelper::encriptar($email);
        $ciEnc = CifradoHelper::encriptar($ci);
        
        $query = "SELECT ID_Usuario FROM usuario WHERE email = ? OR ci = ?";
        $stmt = $this->connection->prepare($query);
        $stmt->execute([$emailEnc, $ciEnc]);
        
        return $stmt->rowCount() > 0;
    }

    private function insertarUsuario($datos) {
        $ciEnc = CifradoHelper::encriptar($datos['ci']);
        $emailEnc = CifradoHelper::encriptar($datos['email']);
        $contrasenaHash = password_hash($datos['contrasena'], PASSWORD_BCRYPT);

        // Generar UUID con PDO
        $stmtUuid = $this->connection->query("SELECT UUID() as uuid");
        $uuidRow = $stmtUuid->fetch(\PDO::FETCH_ASSOC);
        $userId = $uuidRow['uuid'];

        $query = "INSERT INTO usuario (ID_Usuario, nombre, apellido, ci, email, contrasena, tipo, usuario_asignado, estado)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->connection->prepare($query);
        $stmt->execute([
            $userId,
            $datos['nombre'],
            $datos['apellido'],
            $ciEnc,
            $emailEnc,
            $contrasenaHash,
            $datos['tipo'],
            $datos['usuario_asignado'],
            $datos['estado']
        ]);

        echo "  ✓ Usuario {$datos['usuario_asignado']} creado correctamente\n";

        switch ($datos['tipo']) {
            case 'Tecnico':
                if (isset($datos['especialidad'])) {
                    $this->insertarTecnico($userId, $datos['especialidad']);
                    echo "    Especialidad: {$datos['especialidad']}\n";
                }
                break;
            case 'Logistica':
                $this->insertarLogistica($userId);
                echo "    Registrado como logistica\n";
                break;
        }

        return $userId;
    }

    private function insertarTecnico($userId, $especialidad) {
        $query = "INSERT INTO Tecnico (ID_Tecnico, Especialidad, Cantidad_Actividades) VALUES (?, ?, 0)";
        $stmt = $this->connection->prepare($query);
        $stmt->execute([$userId, $especialidad]);
    }

    private function insertarLogistica($userId) {
        $query = "INSERT INTO Logistica (ID_Logistica) VALUES (?)";
        $stmt = $this->connection->prepare($query);
        $stmt->execute([$userId]);
    }
}