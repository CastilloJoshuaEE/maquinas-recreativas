<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helper/CifradoHelper.php';

class AdministradorModel {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function obtenerUsuarioPorId($id) {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT u.*, t.Especialidad 
                FROM usuario u 
                LEFT JOIN Tecnico t ON u.ID_Usuario = t.ID_Tecnico 
                WHERE u.ID_Usuario = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $usuario = $result->fetch_assoc();
            if (isset($usuario['email'])) {
                $usuario['email'] = CifradoHelper::desencriptar($usuario['email']);
            }
            if (isset($usuario['ci']) && !empty($usuario['ci'])) {
                $usuario['ci'] = CifradoHelper::desencriptar($usuario['ci']);
            }
            return $usuario;
        }
        return false;
    }
    public function obtenerTodosUsuarios() {
        $conn = $this->db->getConnection();
        
        //  Eliminado ORDER BY fecha_registro
        $sql = "SELECT u.*, t.Especialidad 
                FROM usuario u 
                LEFT JOIN Tecnico t ON u.ID_Usuario = t.ID_Tecnico 
                ORDER BY u.nombre ASC";
        $result = $conn->query($sql);
        $usuarios = [];
        
        while ($row = $result->fetch_assoc()) {
            if (isset($row['email'])) {
                $row['email'] = CifradoHelper::desencriptar($row['email']);
            }
            if (isset($row['ci']) && !empty($row['ci'])) {
                $row['ci'] = CifradoHelper::desencriptar($row['ci']);
            }
            $usuarios[] = $row;
        }
        
        return $usuarios;
    }
    public function actualizarUsuario($data) {
        $conn = $this->db->getConnection();

        if (!isset($data['ID_Usuario'], $data['nombre'], $data['apellido'], $data['email'], $data['ci'], $data['tipo'], $data['estado'], $data['usuario_asignado'])) {
            return false;
        }

        $especialidad = $data['especialidad'] ?? null;
        $contrasena = $data['contrasena'] ?? null;

        $emailEncriptado = CifradoHelper::encriptar($data['email']);
        $ciEncriptado = CifradoHelper::encriptar($data['ci']);

        try {
            $conn->begin_transaction();

            // Actualizar contraseña si se proporciona
            if (!empty($contrasena)) {
                $hash = password_hash($contrasena, PASSWORD_DEFAULT);
                $sqlPass = "UPDATE usuario SET contrasena = ? WHERE ID_Usuario = ?";
                $stmtPass = $conn->prepare($sqlPass);
                $stmtPass->bind_param("ss", $hash, $data['ID_Usuario']);
                $stmtPass->execute();
            }

            // Actualizar usuario
            $sql = "UPDATE usuario SET 
                    nombre = ?,
                    apellido = ?,
                    email = ?,
                    ci = ?,
                    tipo = ?,
                    estado = ?,
                    usuario_asignado = ?
                    WHERE ID_Usuario = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                "ssssssss",
                $data['nombre'],
                $data['apellido'],
                $emailEncriptado,
                $ciEncriptado,
                $data['tipo'],
                $data['estado'],
                $data['usuario_asignado'],
                $data['ID_Usuario']
            );
            $stmt->execute();

            // Actualizar especialidad si es técnico
            if ($data['tipo'] === 'tecnico') {
                // Verificar si ya existe en tabla tecnico
                $checkSql = "SELECT ID_Tecnico FROM tecnico WHERE ID_Tecnico = ?";
                $checkStmt = $conn->prepare($checkSql);
                $checkStmt->bind_param("s", $data['ID_Usuario']);
                $checkStmt->execute();
                $checkResult = $checkStmt->get_result();

                if ($checkResult->num_rows > 0) {
                    // Actualizar técnico existente
                    if ($especialidad !== null) {
                        $updateTecSql = "UPDATE tecnico SET especialidad = ? WHERE ID_Tecnico = ?";
                        $updateTecStmt = $conn->prepare($updateTecSql);
                        $updateTecStmt->bind_param("ss", $especialidad, $data['ID_Usuario']);
                        $updateTecStmt->execute();
                    }
                } else {
                    // Insertar nuevo técnico
                    if ($especialidad !== null) {
                        $insertTecSql = "INSERT INTO tecnico (ID_Tecnico, especialidad) VALUES (?, ?)";
                        $insertTecStmt = $conn->prepare($insertTecSql);
                        $insertTecStmt->bind_param("ss", $data['ID_Usuario'], $especialidad);
                        $insertTecStmt->execute();
                    }
                }
            } else {
                // Si ya no es técnico, eliminar de tabla tecnico
                $deleteTecSql = "DELETE FROM tecnico WHERE ID_Tecnico = ?";
                $deleteTecStmt = $conn->prepare($deleteTecSql);
                $deleteTecStmt->bind_param("s", $data['ID_Usuario']);
                $deleteTecStmt->execute();
            }

            $conn->commit();
            return true;

        } catch (Exception $e) {
            $conn->rollback();
            error_log("Error en actualizarUsuario: " . $e->getMessage());
            throw $e;
        }
    }

    public function obtenerTipoUsuario($id) {
        $conn = $this->db->getConnection();

        $sql = "SELECT tipo FROM usuario WHERE ID_Usuario = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }

        return false;
    }
      public function eliminarUsuario($id) {
        $conn = $this->db->getConnection();
        
        try {
            $conn->begin_transaction();

            // Verificar si tiene máquinas asignadas
            $checkMaquinasSql = "SELECT COUNT(*) as count FROM MaquinaRecreativa 
                                 WHERE ID_Tecnico_Ensamblador = ? OR ID_Tecnico_Comprobador = ? OR ID_Tecnico_Mantenimiento = ?";
            $checkMaquinasStmt = $conn->prepare($checkMaquinasSql);
            $checkMaquinasStmt->bind_param("sss", $id, $id, $id);
            $checkMaquinasStmt->execute();
            $result = $checkMaquinasStmt->get_result();
            $row = $result->fetch_assoc();
            
            if ($row['count'] > 0) {
                throw new Exception('No se puede eliminar el usuario porque tiene máquinas asignadas');
            }

            // Eliminar de tablas relacionadas
            $tables = [
                'comentario' => 'ID_Usuario_Emisor',
                'notificaciones' => 'ID_Usuario',
                'NotificacionMaquinaRecreativa' => 'ID_Destinatario',
                'componente_usuario' => 'ID_Usuario',
                'inicio_sesion' => 'ID_Usuario',
                'historial_actividades' => 'ID_Usuario',
                'Tecnico' => 'ID_Tecnico',
                'Logistica' => 'ID_Logistica'
            ];
            
            foreach ($tables as $table => $column) {
                $deleteSql = "DELETE FROM $table WHERE $column = ?";
                $deleteStmt = $conn->prepare($deleteSql);
                $deleteStmt->bind_param("s", $id);
                $deleteStmt->execute();
            }

            // Eliminar reportes relacionados
            $deleteReporteSql = "DELETE FROM reporte WHERE ID_Usuario_Emisor = ? OR ID_Usuario_Destinatario = ?";
            $deleteReporteStmt = $conn->prepare($deleteReporteSql);
            $deleteReporteStmt->bind_param("ss", $id, $id);
            $deleteReporteStmt->execute();

            // Finalmente eliminar usuario
            $sql = "DELETE FROM usuario WHERE ID_Usuario = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $id);
            $result = $stmt->execute();

            $conn->commit();
            return $result;

        } catch (Exception $e) {
            $conn->rollback();
            error_log("Error en eliminarUsuario: " . $e->getMessage());
            throw $e;
        }
    }

public function registrarUsuarioAdmin($data) {
    $conn = $this->db->getConnection();
    
    $contrasenaHash = password_hash($data['contrasena'], PASSWORD_BCRYPT);
    $especialidad = $data['especialidad'] ?? null;
    $ciEncriptado = CifradoHelper::encriptar($data['ci']);
    $emailEncriptado = CifradoHelper::encriptar($data['email']);

    try {
        $conn->begin_transaction();

        // Verificar duplicados
        $checkEmailSql = "SELECT ID_Usuario FROM usuario WHERE email = ?";
        $checkEmailStmt = $conn->prepare($checkEmailSql);
        $checkEmailStmt->bind_param("s", $emailEncriptado);
        $checkEmailStmt->execute();
        if ($checkEmailStmt->get_result()->num_rows > 0) {
            $conn->rollback();
            return ['success' => false, 'message' => 'El correo electrónico ya está registrado'];
        }

        $checkUserSql = "SELECT ID_Usuario FROM usuario WHERE usuario_asignado = ?";
        $checkUserStmt = $conn->prepare($checkUserSql);
        $checkUserStmt->bind_param("s", $data['usuario_asignado']);
        $checkUserStmt->execute();
        if ($checkUserStmt->get_result()->num_rows > 0) {
            $conn->rollback();
            return ['success' => false, 'message' => 'El nombre de usuario ya está en uso'];
        }

        // Insertar usuario
        $sql = "INSERT INTO usuario (ID_Usuario, nombre, apellido, ci, email, usuario_asignado, contrasena, tipo, estado) 
                VALUES (UUID(), ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "ssssssss",
            $data['nombre'],
            $data['apellido'],
            $ciEncriptado,
            $emailEncriptado,
            $data['usuario_asignado'],
            $contrasenaHash,
            $data['tipo'],
            $data['estado']
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Error al insertar usuario: " . $stmt->error);
        }
        
        // Obtener el UUID generado
        $id_usuario = null;
        $maxAttempts = 3;
        $attempt = 0;
        
        while ($id_usuario === null && $attempt < $maxAttempts) {
            $getIdSql = "SELECT ID_Usuario FROM usuario WHERE usuario_asignado = ? ORDER BY fecha_registro DESC LIMIT 1";
            $getIdStmt = $conn->prepare($getIdSql);
            $getIdStmt->bind_param("s", $data['usuario_asignado']);
            $getIdStmt->execute();
            $result = $getIdStmt->get_result();
            
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $id_usuario = $row['ID_Usuario'];
            } else {
                $attempt++;
                if ($attempt < $maxAttempts) {
                    usleep(100000); // 0.1 segundos
                }
            }
        }
        
        if ($id_usuario === null) {
            throw new Exception("No se pudo obtener el ID del usuario insertado después de $maxAttempts intentos");
        }

        // Si es técnico, insertar en tabla Tecnico
        if ($data['tipo'] === 'Tecnico' && $especialidad !== null) {
            $sqlTec = "INSERT INTO Tecnico (ID_Tecnico, Especialidad) VALUES (?, ?)";
            $stmtTec = $conn->prepare($sqlTec);
            $stmtTec->bind_param("ss", $id_usuario, $especialidad);
            if (!$stmtTec->execute()) {
                throw new Exception("Error al insertar en Tecnico: " . $stmtTec->error);
            }
        }

        $conn->commit();
        
        return [
            'success' => true,
            'id' => $id_usuario,
            'message' => 'Usuario registrado correctamente'
        ];

    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error en registrarUsuarioAdmin: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error al registrar el usuario: ' . $e->getMessage()];
    }
}
    public function getUsuarios($f) {
        $conn = $this->db->getConnection();
        
        $ci = (isset($f['ci']) && !empty($f['ci'])) ? CifradoHelper::encriptar($f['ci']) : null;
        $estado = $f['estado'] ?? null;
        $tipo = $f['tipo'] ?? null;
        $rango = $f['rango'] ?? null;
        
        $sql = "SELECT u.*, t.Especialidad FROM usuario u 
                LEFT JOIN Tecnico t ON u.ID_Usuario = t.ID_Tecnico 
                WHERE 1=1";
        $params = [];
        $types = "";

        if ($ci) {
            $sql .= " AND u.ci = ?";
            $params[] = $ci;
            $types .= "s";
        }
        if ($estado && $estado !== 'todos') {
            $sql .= " AND u.estado = ?";
            $params[] = $estado;
            $types .= "s";
        }
        if ($tipo && $tipo !== 'todos') {
            $sql .= " AND u.tipo = ?";
            $params[] = $tipo;
            $types .= "s";
        }

        $sql .= " ORDER BY u.nombre ASC";

        $stmt = $conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        
        $usuarios = [];
        while ($row = $result->fetch_assoc()) {
            if (isset($row['email'])) {
                $row['email'] = CifradoHelper::desencriptar($row['email']);
            }
            if (isset($row['ci']) && !empty($row['ci'])) {
                $row['ci'] = CifradoHelper::desencriptar($row['ci']);
            }
            $usuarios[] = $row;
        }
        return $usuarios;
    }

    public function cambiarEstadoUsuario($id, $estado) {
        $conn = $this->db->getConnection();
        
        $sql = "UPDATE usuario SET estado = ? WHERE ID_Usuario = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $estado, $id);
        
        return $stmt->execute();
    }


}
?>