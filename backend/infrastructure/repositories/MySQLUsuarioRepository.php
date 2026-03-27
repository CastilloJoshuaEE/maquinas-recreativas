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
use PDO;
use PDOException;

/**
 * Implementación en MySQL del repositorio de Usuarios.
 */
class MySQLUsuarioRepository implements UsuarioRepository
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * @inheritDoc
     */
    public function save(Usuario $usuario): void
    {
        $conn = $this->db->getConnection();
        
        try {
            $conn->beginTransaction();

            $existing = $this->searchById($usuario->getId());
            
            if ($existing) {
                $sql = "UPDATE usuario SET 
                        nombre = :nombre,
                        apellido = :apellido,
                        ci = :ci,
                        email = :email,
                        usuario_asignado = :usuario_asignado,
                        contrasena = :contrasena,
                        tipo = :tipo,
                        estado = :estado
                        WHERE ID_Usuario = :id";
                
                $stmt = $conn->prepare($sql);
                $stmt->execute([
                    'id' => $usuario->getId()->value(),
                    'nombre' => $usuario->getNombre(),
                    'apellido' => $usuario->getApellido(),
                    'ci' => $usuario->getCi(),
                    'email' => CifradoHelper::encriptar($usuario->getEmail()->value()),
                    'usuario_asignado' => $usuario->getUsuarioAsignado(),
                    'contrasena' => $usuario->getContrasenaHash(),
                    'tipo' => $usuario->getTipo()->value(),
                    'estado' => $usuario->getEstado()->value()
                ]);
            } else {
                $sql = "INSERT INTO usuario 
                        (ID_Usuario, nombre, apellido, ci, email, usuario_asignado, contrasena, tipo, estado)
                        VALUES 
                        (:id, :nombre, :apellido, :ci, :email, :usuario_asignado, :contrasena, :tipo, :estado)";
                
                $stmt = $conn->prepare($sql);
                $stmt->execute([
                    'id' => $usuario->getId()->value(),
                    'nombre' => $usuario->getNombre(),
                    'apellido' => $usuario->getApellido(),
                    'ci' => $usuario->getCi(),
                    'email' => CifradoHelper::encriptar($usuario->getEmail()->value()),
                    'usuario_asignado' => $usuario->getUsuarioAsignado(),
                    'contrasena' => $usuario->getContrasenaHash(),
                    'tipo' => $usuario->getTipo()->value(),
                    'estado' => $usuario->getEstado()->value()
                ]);
            }

            $this->saveSpecificUserData($conn, $usuario);
            $conn->commit();
        } catch (PDOException $e) {
            $conn->rollBack();
            throw new \RuntimeException("Error al guardar usuario: " . $e->getMessage(), 0, $e);
        }
    }

    private function saveSpecificUserData(PDO $conn, Usuario $usuario): void
    {
        $id = $usuario->getId()->value();

        if ($usuario instanceof Tecnico) {
            $checkSql = "SELECT ID_Tecnico FROM Tecnico WHERE ID_Tecnico = :id";
            $checkStmt = $conn->prepare($checkSql);
            $checkStmt->execute(['id' => $id]);
            
            if ($checkStmt->fetch()) {
                $sql = "UPDATE Tecnico SET 
                        Especialidad = :especialidad,
                        Cantidad_Actividades = :actividades
                        WHERE ID_Tecnico = :id";
            } else {
                $sql = "INSERT INTO Tecnico (ID_Tecnico, Especialidad, Cantidad_Actividades) 
                        VALUES (:id, :especialidad, :actividades)";
            }
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                'id' => $id,
                'especialidad' => $usuario->getEspecialidad(),
                'actividades' => $usuario->getCantidadActividades()
            ]);
        } elseif ($usuario instanceof Logistica) {
            $checkSql = "SELECT ID_Logistica FROM Logistica WHERE ID_Logistica = :id";
            $checkStmt = $conn->prepare($checkSql);
            $checkStmt->execute(['id' => $id]);
            
            if (!$checkStmt->fetch()) {
                $sql = "INSERT INTO Logistica (ID_Logistica) VALUES (:id)";
                $stmt = $conn->prepare($sql);
                $stmt->execute(['id' => $id]);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function searchById(Uuid $id): ?Usuario
    {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT u.*, t.Especialidad, t.Cantidad_Actividades 
                FROM usuario u 
                LEFT JOIN Tecnico t ON u.ID_Usuario = t.ID_Tecnico 
                WHERE u.ID_Usuario = :id";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute(['id' => $id->value()]);
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$row) {
            return null;
        }

        return $this->hydrate($row);
    }

    /**
     * @inheritDoc
     */
    public function searchByEmail(string $email): ?Usuario
    {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT u.*, t.Especialidad, t.Cantidad_Actividades 
                FROM usuario u 
                LEFT JOIN Tecnico t ON u.ID_Usuario = t.ID_Tecnico 
                WHERE u.email = :email";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute(['email' => $email]);
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$row) {
            return null;
        }

        return $this->hydrate($row);
    }

    /**
     * @inheritDoc
     */
    public function searchByUsuarioAsignado(string $usuarioAsignado): ?Usuario
    {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT u.*, t.Especialidad, t.Cantidad_Actividades 
                FROM usuario u 
                LEFT JOIN Tecnico t ON u.ID_Usuario = t.ID_Tecnico 
                WHERE u.usuario_asignado = :usuario_asignado";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute(['usuario_asignado' => $usuarioAsignado]);
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$row) {
            return null;
        }

        return $this->hydrate($row);
    }

    /**
     * @inheritDoc
     */
    public function delete(Uuid $id): void
    {
        $conn = $this->db->getConnection();
        
        try {
            $conn->beginTransaction();

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
                $sql = "DELETE FROM {$table} WHERE {$column} = :id";
                $stmt = $conn->prepare($sql);
                $stmt->execute(['id' => $id->value()]);
            }

            $sqlReporte = "DELETE FROM reporte WHERE ID_Usuario_Emisor = :id OR ID_Usuario_Destinatario = :id";
            $stmtReporte = $conn->prepare($sqlReporte);
            $stmtReporte->execute(['id' => $id->value()]);

            $sqlUsuario = "DELETE FROM usuario WHERE ID_Usuario = :id";
            $stmtUsuario = $conn->prepare($sqlUsuario);
            $stmtUsuario->execute(['id' => $id->value()]);

            $conn->commit();
        } catch (PDOException $e) {
            $conn->rollBack();
            throw new \RuntimeException("Error al eliminar usuario: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function findAll(array $filtros = []): array
    {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT u.*, t.Especialidad, t.Cantidad_Actividades 
                FROM usuario u 
                LEFT JOIN Tecnico t ON u.ID_Usuario = t.ID_Tecnico 
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filtros['tipo'])) {
            $sql .= " AND u.tipo = :tipo";
            $params[':tipo'] = $filtros['tipo'];
        }
        
        if (!empty($filtros['estado'])) {
            $sql .= " AND u.estado = :estado";
            $params[':estado'] = $filtros['estado'];
        }
        
        if (!empty($filtros['ci'])) {
            $sql .= " AND u.ci = :ci";
            $params[':ci'] = $filtros['ci'];
        }
        
        $sql .= " ORDER BY u.nombre ASC";
        
        if (isset($filtros['limit'])) {
            $sql .= " LIMIT :limit";
            $params[':limit'] = (int)$filtros['limit'];
        }
        
        if (isset($filtros['offset'])) {
            $sql .= " OFFSET :offset";
            $params[':offset'] = (int)$filtros['offset'];
        }
        
        $stmt = $conn->prepare($sql);
        
        foreach ($params as $key => $value) {
            if (is_int($value)) {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $value);
            }
        }
        
        $stmt->execute();
        
        $usuarios = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $usuarios[] = $this->hydrate($row);
        }
        
        return $usuarios;
    }

    /**
     * Método adicional para obtener usuarios por tipo
     */
    public function findByTipo(string $tipo, ?Uuid $excluirId = null): array
    {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT u.*, t.Especialidad, t.Cantidad_Actividades 
                FROM usuario u 
                LEFT JOIN Tecnico t ON u.ID_Usuario = t.ID_Tecnico 
                WHERE u.tipo = :tipo";
        
        $params = [':tipo' => $tipo];
        
        if ($excluirId) {
            $sql .= " AND u.ID_Usuario != :excluirId";
            $params[':excluirId'] = $excluirId->value();
        }
        
        $sql .= " ORDER BY u.nombre ASC";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        
        $usuarios = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $usuarios[] = $this->hydrate($row);
        }
        
        return $usuarios;
    }

    /**
     * Método adicional para verificar existencia por email
     */
    public function existsByEmail(string $emailEncriptado): bool
    {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT COUNT(*) as total FROM usuario WHERE email = :email";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':email' => $emailEncriptado]);
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'] > 0;
    }

    /**
     * Método adicional para verificar existencia por CI
     */
    public function existsByCi(string $ciEncriptada): bool
    {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT COUNT(*) as total FROM usuario WHERE ci = :ci";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':ci' => $ciEncriptada]);
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'] > 0;
    }

    /**
     * Método adicional para verificar existencia por usuario asignado
     */
    public function existsByUsuarioAsignado(string $usuarioAsignado): bool
    {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT COUNT(*) as total FROM usuario WHERE usuario_asignado = :usuario_asignado";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':usuario_asignado' => $usuarioAsignado]);
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'] > 0;
    }

    /**
     * Método adicional para verificar existencia por usuario asignado excluyendo un ID
     */
    public function existsByUsuarioAsignadoAndNotId(string $usuarioAsignado, Uuid $id): bool
    {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT COUNT(*) as total FROM usuario 
                WHERE usuario_asignado = :usuario_asignado AND ID_Usuario != :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':usuario_asignado' => $usuarioAsignado,
            ':id' => $id->value()
        ]);
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'] > 0;
    }

    /**
     * Método adicional para verificar si tiene máquinas asignadas
     */
    public function hasMachinesAssigned(Uuid $id): bool
    {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT COUNT(*) as total FROM MaquinaRecreativa 
                WHERE ID_Tecnico_Ensamblador = :id 
                   OR ID_Tecnico_Comprobador = :id 
                   OR ID_Tecnico_Mantenimiento = :id";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([':id' => $id->value()]);
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'] > 0;
    }

    /**
     * Método adicional para registrar logout
     */
    public function registrarLogout(Uuid $id): void
    {
        $conn = $this->db->getConnection();
        
        $sql = "UPDATE inicio_sesion SET fecha_ultima_sesion = NOW() 
                WHERE ID_Usuario = :id AND fecha_ultima_sesion IS NULL 
                ORDER BY fecha_inicio DESC LIMIT 1";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([':id' => $id->value()]);
    }

    /**
     * Método adicional para registrar actividad
     */
    public function registrarActividad(Uuid $id, string $descripcion): void
    {
        $conn = $this->db->getConnection();
        
        $sql = "INSERT INTO historial_actividades (ID_Usuario, descripcion, fecha_registro) 
                VALUES (:id, :descripcion, NOW())";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':id' => $id->value(),
            ':descripcion' => $descripcion
        ]);
    }

    /**
     * Método adicional para obtener historial de actividades
     */
    public function obtenerHistorialActividades(Uuid $id): array
    {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT * FROM historial_actividades 
                WHERE ID_Usuario = :id 
                ORDER BY fecha_registro DESC 
                LIMIT 50";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([':id' => $id->value()]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Hidrata un objeto Usuario a partir de un array de datos.
     */
    public function hydrate(array $row): Usuario
    {
        $id = new Uuid($row['ID_Usuario']);
        $email = new Email(CifradoHelper::desencriptar($row['email']));
        $tipo = new TipoUsuario($row['tipo']);
        $estado = new EstadoUsuario($row['estado']);
        $ci = isset($row['ci']) ? CifradoHelper::desencriptar($row['ci']) : '';

        switch ($tipo->value()) {
            case TipoUsuario::TECNICO:
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
            case TipoUsuario::LOGISTICA:
                return new Logistica(
                    $id,
                    $row['nombre'],
                    $row['apellido'],
                    $ci,
                    $email,
                    $row['usuario_asignado'],
                    $row['contrasena'],
                    $estado
                );
            default:
                return new Usuario(
                    $id,
                    $row['nombre'],
                    $row['apellido'],
                    $ci,
                    $email,
                    $row['usuario_asignado'],
                    $row['contrasena'],
                    $tipo,
                    $estado
                );
        }
    }
}