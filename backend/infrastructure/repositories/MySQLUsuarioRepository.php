<?php
declare(strict_types=1);

namespace maquinas_recreativas\Infrastructure\Repositories;

use maquinas_recreativas\Domain\Usuario\Usuario;
use maquinas_recreativas\Domain\Usuario\Tecnico;
use maquinas_recreativas\Domain\Usuario\Logistica;
use maquinas_recreativas\Domain\Usuario\TipoUsuario;
use maquinas_recreativas\Domain\Usuario\EstadoUsuario;
use maquinas_recreativas\Domain\Usuario\UsuarioRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Domain\Shared\ValueObjects\Email;
use maquinas_recreativas\Infrastructure\Database\Database;
use maquinas_recreativas\Infrastructure\Security\CifradoHelper;

class MySQLUsuarioRepository implements UsuarioRepository
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

 public function save(Usuario $usuario): void
{
    $conn = $this->db->getConnection();

    // Detectar si ya existe una transacción activa (p.ej. la del test)
    $txResult     = $conn->query("SELECT @@in_transaction AS in_tx");
    $txRow        = $txResult->fetch_assoc();
    $outerTx      = (bool)($txRow['in_tx'] ?? false);

    try {
        if (!$outerTx) {
            $conn->begin_transaction();
        }

        $existing = $this->searchById($usuario->getId());

        $nombre          = $usuario->getNombre();
        $apellido        = $usuario->getApellido();
        $ci              = $usuario->getCi();
        $emailEncriptado = CifradoHelper::encriptar($usuario->getEmail()->value());
        $usuarioAsignado = $usuario->getUsuarioAsignado();
        $contrasenaHash  = $usuario->getContrasenaHash();
        $tipo            = $usuario->getTipo()->value();
        $estado          = $usuario->getEstado()->value();
        $id              = $usuario->getId()->value();

        if ($existing) {
            $sql  = "UPDATE usuario SET 
                        nombre = ?, apellido = ?, ci = ?, email = ?,
                        usuario_asignado = ?, contrasena = ?, tipo = ?, estado = ?
                     WHERE ID_Usuario = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('sssssssss',
                $nombre, $apellido, $ci, $emailEncriptado,
                $usuarioAsignado, $contrasenaHash, $tipo, $estado, $id
            );
        } else {
            $sql  = "INSERT INTO usuario 
                        (ID_Usuario, nombre, apellido, ci, email, usuario_asignado, contrasena, tipo, estado)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('sssssssss',
                $id, $nombre, $apellido, $ci, $emailEncriptado,
                $usuarioAsignado, $contrasenaHash, $tipo, $estado
            );
        }
        $stmt->execute();
        $stmt->close();

        $this->saveSpecificUserData($conn, $usuario);

        if (!$outerTx) {
            $conn->commit();
        }
    } catch (\Exception $e) {
        if (!$outerTx) {
            $conn->rollback();
        }
        throw new \RuntimeException("Error al guardar usuario: " . $e->getMessage(), 0, $e);
    }
}

    private function saveSpecificUserData(\mysqli $conn, Usuario $usuario): void
    {
        $id = $usuario->getId()->value();

        if ($usuario instanceof Tecnico) {
            $checkSql = "SELECT ID_Tecnico FROM Tecnico WHERE ID_Tecnico = ?";
            $checkStmt = $conn->prepare($checkSql);
            $checkStmt->bind_param('s', $id);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            $exists = $checkResult->num_rows > 0;
            $checkStmt->close();

            $especialidad = $usuario->getEspecialidad();
            $cantidadActividades = $usuario->getCantidadActividades();
            
            if ($exists) {
                $sql = "UPDATE Tecnico SET Especialidad = ?, Cantidad_Actividades = ? WHERE ID_Tecnico = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('sis', $especialidad, $cantidadActividades, $id);
            } else {
                $sql = "INSERT INTO Tecnico (ID_Tecnico, Especialidad, Cantidad_Actividades) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('ssi', $id, $especialidad, $cantidadActividades);
            }
            $stmt->execute();
            $stmt->close();
        } elseif ($usuario instanceof Logistica) {
            $checkSql = "SELECT ID_Logistica FROM Logistica WHERE ID_Logistica = ?";
            $checkStmt = $conn->prepare($checkSql);
            $checkStmt->bind_param('s', $id);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            $exists = $checkResult->num_rows > 0;
            $checkStmt->close();

            if (!$exists) {
                $sql = "INSERT INTO Logistica (ID_Logistica) VALUES (?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('s', $id);
                $stmt->execute();
                $stmt->close();
            }
        }
    }

    public function searchById(Uuid $id): ?Usuario
    {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT u.*, t.Especialidad, t.Cantidad_Actividades 
                FROM usuario u 
                LEFT JOIN Tecnico t ON u.ID_Usuario = t.ID_Tecnico 
                WHERE u.ID_Usuario = ?";
        
        $stmt = $conn->prepare($sql);
        $idValue = $id->value();
        $stmt->bind_param('s', $idValue);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        if (!$row) {
            return null;
        }

        return $this->hydrate($row);
    }

    public function findById(Uuid $id): ?Usuario
    {
        return $this->searchById($id);
    }

    public function searchByEmail(string $email): ?Usuario
    {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT u.*, t.Especialidad, t.Cantidad_Actividades 
                FROM usuario u 
                LEFT JOIN Tecnico t ON u.ID_Usuario = t.ID_Tecnico 
                WHERE u.email = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        if (!$row) {
            return null;
        }

        return $this->hydrate($row);
    }

    public function findByEmail(string $email): ?Usuario
    {
        return $this->searchByEmail($email);
    }

    public function searchByUsuarioAsignado(string $usuarioAsignado): ?Usuario
    {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT u.*, t.Especialidad, t.Cantidad_Actividades 
                FROM usuario u 
                LEFT JOIN Tecnico t ON u.ID_Usuario = t.ID_Tecnico 
                WHERE u.usuario_asignado = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $usuarioAsignado);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        if (!$row) {
            return null;
        }
        
        return $this->hydrate($row);
    }

    public function delete(Uuid $id): void
    {
        $conn = $this->db->getConnection();
        
        try {
            $conn->begin_transaction();

            $idValue = $id->value();

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
                $sql = "DELETE FROM {$table} WHERE {$column} = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('s', $idValue);
                $stmt->execute();
                $stmt->close();
            }

            $sqlReporte = "DELETE FROM reporte WHERE ID_Usuario_Emisor = ? OR ID_Usuario_Destinatario = ?";
            $stmtReporte = $conn->prepare($sqlReporte);
            $stmtReporte->bind_param('ss', $idValue, $idValue);
            $stmtReporte->execute();
            $stmtReporte->close();

            $sqlUsuario = "DELETE FROM usuario WHERE ID_Usuario = ?";
            $stmtUsuario = $conn->prepare($sqlUsuario);
            $stmtUsuario->bind_param('s', $idValue);
            $stmtUsuario->execute();
            $stmtUsuario->close();

            $conn->commit();
        } catch (\Exception $e) {
            $conn->rollback();
            throw new \RuntimeException("Error al eliminar usuario: " . $e->getMessage(), 0, $e);
        }
    }

    public function findAll(array $filtros = []): array
    {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT u.*, t.Especialidad, t.Cantidad_Actividades 
                FROM usuario u 
                LEFT JOIN Tecnico t ON u.ID_Usuario = t.ID_Tecnico 
                WHERE 1=1";
        
        $params = [];
        $types = "";
        
        if (!empty($filtros['tipo'])) {
            $sql .= " AND u.tipo = ?";
            $params[] = $filtros['tipo'];
            $types .= "s";
        }
        
        if (!empty($filtros['estado'])) {
            $sql .= " AND u.estado = ?";
            $params[] = $filtros['estado'];
            $types .= "s";
        }
        
        if (!empty($filtros['ci'])) {
            $sql .= " AND u.ci = ?";
            $params[] = $filtros['ci'];
            $types .= "s";
        }
        
        $sql .= " ORDER BY u.nombre ASC";
        
        if (isset($filtros['limit'])) {
            $sql .= " LIMIT ?";
            $params[] = (int)$filtros['limit'];
            $types .= "i";
        }
        
        if (isset($filtros['offset'])) {
            $sql .= " OFFSET ?";
            $params[] = (int)$filtros['offset'];
            $types .= "i";
        }
        
        $stmt = $conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        
        $usuarios = [];
        while ($row = $result->fetch_assoc()) {
            $usuarios[] = $this->hydrate($row);
        }
        $stmt->close();
        
        return $usuarios;
    }

    public function findByTipo(string $tipo, ?Uuid $excluirId = null): array
    {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT u.*, t.Especialidad, t.Cantidad_Actividades 
                FROM usuario u 
                LEFT JOIN Tecnico t ON u.ID_Usuario = t.ID_Tecnico 
                WHERE u.tipo = ?";
        
        $params = [$tipo];
        $types = "s";
        
        if ($excluirId) {
            $sql .= " AND u.ID_Usuario != ?";
            $excluirIdValue = $excluirId->value();
            $params[] = $excluirIdValue;
            $types .= "s";
        }
        
        $sql .= " ORDER BY u.nombre ASC";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $usuarios = [];
        while ($row = $result->fetch_assoc()) {
            $usuarios[] = $this->hydrate($row);
        }
        $stmt->close();
        
        return $usuarios;
    }

    public function findTecnicosByEspecialidad(string $especialidad): array
    {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT u.*, t.Especialidad, t.Cantidad_Actividades 
                FROM usuario u
                INNER JOIN Tecnico t ON u.ID_Usuario = t.ID_Tecnico
                WHERE t.Especialidad = ? AND u.tipo = 'Tecnico'
                ORDER BY u.nombre ASC";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $especialidad);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $tecnicos = [];
        while ($row = $result->fetch_assoc()) {
            $tecnicos[] = $this->hydrateTecnico($row);
        }
        $stmt->close();
        
        return $tecnicos;
    }

    private function hydrateTecnico(array $row): Tecnico
    {
        $id = new Uuid($row['ID_Usuario']);
        $email = new Email(CifradoHelper::desencriptar($row['email']));
        $estado = new EstadoUsuario($row['estado']);
        $ci = isset($row['ci']) ? CifradoHelper::desencriptar($row['ci']) : '';

        return new Tecnico(
            $id,
            $row['nombre'],
            $row['apellido'],
            $ci,
            $email,
            $row['usuario_asignado'],
            $row['contrasena'],
            $estado,
            $row['Especialidad'] ?? '',
            (int)($row['Cantidad_Actividades'] ?? 0)
        );
    }

    public function existsByEmail(string $emailEncriptado): bool
    {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT COUNT(*) as total FROM usuario WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $emailEncriptado);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        return $row['total'] > 0;
    }

    public function existsByCi(string $ciEncriptada): bool
    {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT COUNT(*) as total FROM usuario WHERE ci = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $ciEncriptada);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        return $row['total'] > 0;
    }

    public function existsByUsuarioAsignado(string $usuarioAsignado): bool
    {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT COUNT(*) as total FROM usuario WHERE usuario_asignado = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $usuarioAsignado);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        return $row['total'] > 0;
    }

    public function existsByUsuarioAsignadoAndNotId(string $usuarioAsignado, Uuid $id): bool
    {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT COUNT(*) as total FROM usuario 
                WHERE usuario_asignado = ? AND ID_Usuario != ?";
        $stmt = $conn->prepare($sql);
        $idValue = $id->value();
        $stmt->bind_param('ss', $usuarioAsignado, $idValue);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        return $row['total'] > 0;
    }

    public function hasMachinesAssigned(Uuid $id): bool
    {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT COUNT(*) as total FROM MaquinaRecreativa 
                WHERE ID_Tecnico_Ensamblador = ? 
                   OR ID_Tecnico_Comprobador = ? 
                   OR ID_Tecnico_Mantenimiento = ?";
        
        $stmt = $conn->prepare($sql);
        $idValue = $id->value();
        $stmt->bind_param('sss', $idValue, $idValue, $idValue);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        return $row['total'] > 0;
    }

    public function registrarLogout(Uuid $id): void
    {
        $conn = $this->db->getConnection();
        
        $sql = "UPDATE inicio_sesion SET fecha_ultima_sesion = NOW() 
                WHERE ID_Usuario = ? AND fecha_ultima_sesion IS NULL 
                ORDER BY fecha_inicio DESC LIMIT 1";
        
        $stmt = $conn->prepare($sql);
        $idValue = $id->value();
        $stmt->bind_param('s', $idValue);
        $stmt->execute();
        $stmt->close();
    }

    public function registrarActividad(Uuid $id, string $descripcion): void
    {
        $conn = $this->db->getConnection();
        
        $sql = "INSERT INTO historial_actividades (ID_Usuario, descripcion, fecha_registro) 
                VALUES (?, ?, NOW())";
        
        $stmt = $conn->prepare($sql);
        $idValue = $id->value();
        $stmt->bind_param('ss', $idValue, $descripcion);
        $stmt->execute();
        $stmt->close();
    }

    public function obtenerHistorialActividades(Uuid $id): array
    {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT * FROM historial_actividades 
                WHERE ID_Usuario = ? 
                ORDER BY fecha_registro DESC 
                LIMIT 50";
        
        $stmt = $conn->prepare($sql);
        $idValue = $id->value();
        $stmt->bind_param('s', $idValue);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $actividades = [];
        while ($row = $result->fetch_assoc()) {
            $actividades[] = $row;
        }
        $stmt->close();
        
        return $actividades;
    }

 public function hydrate(array $row): Usuario
{
    $id     = new Uuid($row['ID_Usuario']);
    $email  = new Email(CifradoHelper::desencriptar($row['email']));
    $tipo   = new TipoUsuario($row['tipo']);
    $estado = new EstadoUsuario($row['estado']);
    $ci     = isset($row['ci']) ? CifradoHelper::desencriptar($row['ci']) : '';

    switch ($tipo->value()) {
        case TipoUsuario::TECNICO:
            // Especialidad puede ser NULL si el JOIN no encontró fila en Tecnico
            $especialidad = $row['Especialidad'] ?? null;
            if (empty($especialidad)) {
                // Registrar aviso y degradar a Usuario genérico para no romper la carga
                error_log("AVISO: Técnico {$row['ID_Usuario']} sin fila en tabla Tecnico.");
                return new Usuario($id, $row['nombre'], $row['apellido'], $ci, $email,
                    $row['usuario_asignado'], $row['contrasena'], $tipo, $estado);
            }
            return new Tecnico(
                $id, $row['nombre'], $row['apellido'], $ci, $email,
                $row['usuario_asignado'], $row['contrasena'], $estado,
                $especialidad,
                (int)($row['Cantidad_Actividades'] ?? 0)
            );

        case TipoUsuario::LOGISTICA:
            return new Logistica(
                $id, $row['nombre'], $row['apellido'], $ci, $email,
                $row['usuario_asignado'], $row['contrasena'], $estado
            );

        default:
            return new Usuario(
                $id, $row['nombre'], $row['apellido'], $ci, $email,
                $row['usuario_asignado'], $row['contrasena'], $tipo, $estado
            );
    }
}
}