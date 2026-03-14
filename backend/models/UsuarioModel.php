<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helper/CifradoHelper.php';

class UsuarioModel {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    private function generateUUID($conn) {
        $sql = "SELECT UUID() as uuid";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        return $row['uuid'];
    }
public function registrarUsuario($nombre, $apellido, $ci, $email, $usuario_asignado, $contrasena, $tipo, $especialidad = null) {
        $conn = $this->db->getConnection();
        
        if (empty($nombre) || empty($apellido) || empty($ci) || empty($email) || empty($usuario_asignado) || empty($contrasena) || empty($tipo)) {
            return ['success' => false, 'message' => 'Todos los campos son requeridos'];
        }

        // Validar longitud de contraseña
        if (strlen($contrasena) < 6) {
            throw new ValidacionDatosException(
                "La contraseña debe tener al menos 6 caracteres",
                1200,
                ['min_length' => 6]
            );
        }

        try {
            $hashedPassword = password_hash($contrasena, PASSWORD_DEFAULT);
            $ciEncriptado = CifradoHelper::encriptar($ci);
            $emailEncriptado = CifradoHelper::encriptar($email);
            
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

            $checkCiSql = "SELECT ID_Usuario FROM usuario WHERE ci = ?";
            $checkCiStmt = $conn->prepare($checkCiSql);
            $checkCiStmt->bind_param("s", $ciEncriptado);
            $checkCiStmt->execute();
            if ($checkCiStmt->get_result()->num_rows > 0) {
                $conn->rollback();
                return ['success' => false, 'message' => 'La cédula ya está registrada'];
            }

            $checkUserSql = "SELECT ID_Usuario FROM usuario WHERE usuario_asignado = ?";
            $checkUserStmt = $conn->prepare($checkUserSql);
            $checkUserStmt->bind_param("s", $usuario_asignado);
            $checkUserStmt->execute();
            if ($checkUserStmt->get_result()->num_rows > 0) {
                $conn->rollback();
                return ['success' => false, 'message' => 'El nombre de usuario ya está en uso'];
            }

            $id_usuario = $this->generateUUID($conn);
            
            $sql = "INSERT INTO usuario (ID_Usuario, nombre, apellido, ci, email, usuario_asignado, contrasena, tipo, estado) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Activo')";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                "ssssssss", 
                $id_usuario,
                $nombre,
                $apellido,
                $ciEncriptado,
                $emailEncriptado,
                $usuario_asignado,
                $hashedPassword,
                $tipo
            );

            if (!$stmt->execute()) {
                $conn->rollback();
                return ['success' => false, 'message' => 'Error al registrar el usuario: ' . $stmt->error];
            }

            // Si es técnico, insertar en tabla Tecnico
            if ($tipo === 'Tecnico' && $especialidad !== null) {
                $sqlTec = "INSERT INTO Tecnico (ID_Tecnico, Especialidad) VALUES (?, ?)";
                $stmtTec = $conn->prepare($sqlTec);
                $stmtTec->bind_param("ss", $id_usuario, $especialidad);
                $stmtTec->execute();
            }

            // Si es Logistica, insertar en tabla Logistica
            if ($tipo === 'Logistica') {
                $sqlLog = "INSERT INTO Logistica (ID_Logistica) VALUES (?)";
                $stmtLog = $conn->prepare($sqlLog);
                $stmtLog->bind_param("s", $id_usuario);
                $stmtLog->execute();
            }

            $conn->commit();

            return $id_usuario;

        } catch (ValidacionDatosException $e) {
            throw $e;
        } catch (Exception $e) {
            $conn->rollback();
            error_log("Error en registrarUsuario: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error en el servidor: ' . $e->getMessage()];
        }
    }
    public function login($usuario_asignado) {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT u.*, t.especialidad FROM usuario u 
                LEFT JOIN tecnico t ON u.ID_Usuario = t.ID_Tecnico 
                WHERE u.usuario_asignado = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $usuario_asignado);
        $stmt->execute();
        
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $usuario = $result->fetch_assoc();
            if (isset($usuario['email'])) {
                $usuario['email'] = CifradoHelper::desencriptar($usuario['email']);
            }
            if (isset($usuario['ci'])) {
                $usuario['ci'] = CifradoHelper::desencriptar($usuario['ci']);
            }
            return $usuario;
        }
        
        return false;
    }

    public function registrarInicioSesion($userId, $usuarioAsignado, $contrasenaHash) {
        $conn = $this->db->getConnection();
        
        try {
            $idSesion = $this->generateUUID($conn);
            
            // CORRECCIÓN: Usar la tabla correcta 'inicio_sesion' en lugar de 'sesiones_usuario'
            $sql = "INSERT INTO inicio_sesion (ID_Inicio_Sesion, ID_Usuario, usuario_asignado, contrasena, fecha_inicio) 
                    VALUES (?, ?, ?, ?, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssss", $idSesion, $userId, $usuarioAsignado, $contrasenaHash);
            
            return $stmt->execute();

        } catch (Exception $e) {
            error_log("Error en registrarInicioSesion: " . $e->getMessage());
            return false;
        }
    }

    public function registrarLogout($userId) {
        $conn = $this->db->getConnection();
        
        // CORRECCIÓN: Usar la tabla correcta 'inicio_sesion'
        $sql = "UPDATE inicio_sesion SET fecha_ultima_sesion = NOW() 
                WHERE ID_Usuario = ? AND fecha_ultima_sesion IS NULL 
                ORDER BY fecha_inicio DESC LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $userId);
        
        return $stmt->execute();
    }

    public function obtenerEstadoUsuario($userId) {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT estado FROM usuario WHERE ID_Usuario = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $userId);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['estado'] ?? null;
    }

public function incrementarActividadesTecnico($idTecnico) {
        $conn = $this->db->getConnection();
        
        // CORRECCIÓN: Usar el nombre correcto de la columna 'Cantidad_Actividades'
        $sql = "UPDATE Tecnico SET Cantidad_Actividades = Cantidad_Actividades + 1 
                WHERE ID_Tecnico = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $idTecnico);
        
        return $stmt->execute();
    }

    public function obtenerUsuarioPorId($id) {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT u.*, t.Especialidad FROM usuario u 
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
            if (isset($usuario['ci'])) {
                $usuario['ci'] = CifradoHelper::desencriptar($usuario['ci']);
            }
            return $usuario;
        }
        
        return false;
    }

    public function actualizarPerfil($data) {
        $conn = $this->db->getConnection();

        $idUsuario = $data['ID_Usuario'] ?? $data['id'] ?? null;

        if (!isset($idUsuario, $data['nombre'], $data['apellido'], $data['email'], $data['ci'], $data['tipo'], $data['estado'])) {
            error_log("Datos faltantes en actualizarPerfil: " . print_r($data, true));
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
                $contrasenaHash = password_hash($contrasena, PASSWORD_DEFAULT);
                $sql = "UPDATE usuario SET contrasena = ? WHERE ID_Usuario = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ss", $contrasenaHash, $idUsuario);
                if (!$stmt->execute()) {
                    throw new Exception("Error al actualizar contraseña: " . $stmt->error);
                }
            }

            // Actualizar datos principales
            $sql = "UPDATE usuario SET 
                    nombre = ?,
                    apellido = ?,
                    email = ?,
                    ci = ?,
                    tipo = ?,
                    estado = ?
                    WHERE ID_Usuario = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                "sssssss",
                $data['nombre'],
                $data['apellido'],
                $emailEncriptado,
                $ciEncriptado,
                $data['tipo'],
                $data['estado'],
                $idUsuario
            );

            if (!$stmt->execute()) {
                throw new Exception("Error al actualizar usuario: " . $stmt->error);
            }

            // Actualizar especialidad si es técnico
            if ($data['tipo'] === 'Tecnico') {
                // Verificar si ya existe en tabla Tecnico
                $checkSql = "SELECT ID_Tecnico FROM Tecnico WHERE ID_Tecnico = ?";
                $checkStmt = $conn->prepare($checkSql);
                $checkStmt->bind_param("s", $idUsuario);
                $checkStmt->execute();
                $checkResult = $checkStmt->get_result();

                if ($checkResult->num_rows > 0) {
                    if ($especialidad !== null) {
                        $updateTecSql = "UPDATE Tecnico SET Especialidad = ? WHERE ID_Tecnico = ?";
                        $updateTecStmt = $conn->prepare($updateTecSql);
                        $updateTecStmt->bind_param("ss", $especialidad, $idUsuario);
                        $updateTecStmt->execute();
                    }
                } else {
                    if ($especialidad !== null) {
                        $insertTecSql = "INSERT INTO Tecnico (ID_Tecnico, Especialidad) VALUES (?, ?)";
                        $insertTecStmt = $conn->prepare($insertTecSql);
                        $insertTecStmt->bind_param("ss", $idUsuario, $especialidad);
                        $insertTecStmt->execute();
                    }
                }
            } else {
                // Si ya no es técnico, eliminar de tabla Tecnico
                $deleteTecSql = "DELETE FROM Tecnico WHERE ID_Tecnico = ?";
                $deleteTecStmt = $conn->prepare($deleteTecSql);
                $deleteTecStmt->bind_param("s", $idUsuario);
                $deleteTecStmt->execute();
            }

            $conn->commit();
            return true;

        } catch (Exception $e) {
            $conn->rollback();
            error_log("Error en actualizarPerfil: " . $e->getMessage());
            return false;
        }
    }

    public function actualizarUsuarioAsignado($data) {
        $conn = $this->db->getConnection();
        
        if (!isset($data['email'], $data['usuario_asignado'])) {
            return ['success' => false, 'message' => 'Datos incompletos'];
        }

        try {
            $emailEncriptado = CifradoHelper::encriptar($data['email']);
            
            $sql = "UPDATE usuario SET usuario_asignado = ? WHERE email = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $data['usuario_asignado'], $emailEncriptado);
            
            if ($stmt->execute() && $stmt->affected_rows > 0) {
                return [
                    'success' => true,
                    'message' => 'Usuario actualizado correctamente'
                ];
            }
            
            return ['success' => false, 'message' => 'Correo electrónico no encontrado'];

        } catch (Exception $e) {
            error_log("Error en actualizarUsuarioAsignado: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error en el servidor: ' . $e->getMessage()];
        }
    }

    public function recuperarContrasena($data) {
        $conn = $this->db->getConnection();
        
        if (!isset($data['email'], $data['nueva_contrasena'])) {
            return ['success' => false, 'message' => 'Datos incompletos'];
        }

        try {
            $contrasenaHash = password_hash($data['nueva_contrasena'], PASSWORD_DEFAULT);
            $emailEncriptado = CifradoHelper::encriptar($data['email']);
            
            $sql = "UPDATE usuario SET contrasena = ? WHERE email = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $contrasenaHash, $emailEncriptado);
            
            if ($stmt->execute() && $stmt->affected_rows > 0) {
                return [
                    'success' => true,
                    'message' => 'Contraseña actualizada correctamente'
                ];
            }
            
            return ['success' => false, 'message' => 'Correo electrónico no encontrado'];

        } catch (Exception $e) {
            error_log("Error en recuperarContrasena: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error en el servidor: ' . $e->getMessage()];
        }
    }

    public function obtenerTecnicosPorEspecialidad($especialidad, $soloActivos = true) {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT u.*, t.Especialidad 
                FROM usuario u
                JOIN Tecnico t ON u.ID_Usuario = t.ID_Tecnico
                WHERE t.Especialidad = ?";
        $params = [$especialidad];
        $types = "s";

        if ($soloActivos) {
            $sql .= " AND u.estado = 'Activo'";
        }

        $sql .= " ORDER BY u.nombre ASC";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $tecnicos = [];
        
        while ($row = $result->fetch_assoc()) {
            if (isset($row['email'])) {
                $row['email'] = CifradoHelper::desencriptar($row['email']);
            }
            if (isset($row['ci'])) {
                $row['ci'] = CifradoHelper::desencriptar($row['ci']);
            }
            $tecnicos[] = $row;
        }
        
        return $tecnicos;
    }

    public function obtenerUsuariosPorTipo($tipo, $excluirId = null) {
        $conn = $this->db->getConnection();

        $sql = "SELECT u.*, t.Especialidad FROM usuario u 
                LEFT JOIN Tecnico t ON u.ID_Usuario = t.ID_Tecnico
                WHERE u.tipo = ?";
        $params = [$tipo];
        $types = "s";

        if ($excluirId) {
            $sql .= " AND u.ID_Usuario != ?";
            $params[] = $excluirId;
            $types .= "s";
        }

        $sql .= " ORDER BY u.nombre ASC";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();

        $result = $stmt->get_result();
        $usuarios = [];

        while ($row = $result->fetch_assoc()) {
            if (isset($row['email'])) {
                $row['email'] = CifradoHelper::desencriptar($row['email']);
            }
            if (isset($row['ci'])) {
                $row['ci'] = CifradoHelper::desencriptar($row['ci']);
            }
            $usuarios[] = $row;
        }

        return $usuarios;
    }


     public function registrarActividad($idUsuario, $descripcion) {
        $conn = $this->db->getConnection();
        
        try {
            $idActividad = $this->generateUUID($conn);
            
            // CORRECCIÓN: Usar la tabla correcta 'historial_actividades'
            $sql = "INSERT INTO historial_actividades (ID_Historial_Actividades, ID_Usuario, descripcion, fecha_registro) 
                    VALUES (?, ?, ?, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $idActividad, $idUsuario, $descripcion);
            
            return $stmt->execute();

        } catch (Exception $e) {
            error_log("Error en registrarActividad: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerHistorialActividades($usuarioId) {
        $conn = $this->db->getConnection();
        
        // CORRECCIÓN: Usar la tabla correcta 'historial_actividades'
        $sql = "SELECT * FROM historial_actividades 
                WHERE ID_Usuario = ? 
                ORDER BY fecha_registro DESC 
                LIMIT 50";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $usuarioId);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $actividades = [];

        while ($row = $result->fetch_assoc()) {
            $actividades[] = $row;
        }

        return $actividades;
    }

    public function buscarPorEmail($email) {
        $conn = $this->db->getConnection();
        $emailEncriptado = CifradoHelper::encriptar($email);

        $sql = "SELECT u.*, t.Especialidad FROM usuario u 
                LEFT JOIN Tecnico t ON u.ID_Usuario = t.ID_Tecnico
                WHERE u.email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $emailEncriptado);
        $stmt->execute();

        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $usuario = $result->fetch_assoc();
            $usuario['email'] = CifradoHelper::desencriptar($usuario['email']);
            $usuario['ci'] = CifradoHelper::desencriptar($usuario['ci']);
            return ['success' => true, 'usuario' => $usuario];
        }

        return ['success' => false, 'message' => 'Usuario no encontrado'];
    }
}
?>