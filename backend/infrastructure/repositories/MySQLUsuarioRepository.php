<?php
/**
 * Infrastructure/Repositories/MySQLUsuarioRepository.php
 *
 * TTL caché: 1800 s — datos estables.
 * Claves:  usuario:id:{uuid}
 *          usuario:email:{md5(email)}
 *          usuario:username:{username}
 *          usuarios:tipo:{tipo}[:excluir:{uuid}]
 *          usuarios:all:{md5(filtros)}
 *          tecnicos:especialidad:{esp}
 * Invalida: save(), delete()
 */
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
use maquinas_recreativas\Infrastructure\Cache\CacheInterface;
use maquinas_recreativas\Infrastructure\Cache\CacheFactory;

class MySQLUsuarioRepository implements UsuarioRepository
{
    private Database       $db;
    private CacheInterface $cache;
    private int            $ttl = 1800;

    public function __construct(Database $db, ?CacheInterface $cache = null)
    {
        $this->db    = $db;
        $this->cache = $cache ?? CacheFactory::create();
    }

    // =========================================================================
    // ESCRITURA
    // =========================================================================

    public function save(Usuario $usuario): void
    {
        $conn = $this->db->getConnection();

        $txResult = $conn->query("SELECT @@in_transaction AS in_tx");
        $txRow    = $txResult->fetch_assoc();
        $outerTx  = (bool)($txRow['in_tx'] ?? false);

        try {
            if (!$outerTx) $conn->begin_transaction();

            $existing        = $this->searchById($usuario->getId());
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
                            nombre=?,apellido=?,ci=?,email=?,
                            usuario_asignado=?,contrasena=?,tipo=?,estado=?
                         WHERE ID_Usuario=?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('sssssssss', $nombre, $apellido, $ci, $emailEncriptado,
                    $usuarioAsignado, $contrasenaHash, $tipo, $estado, $id);
            } else {
                $sql  = "INSERT INTO usuario (ID_Usuario,nombre,apellido,ci,email,usuario_asignado,contrasena,tipo,estado)
                         VALUES (?,?,?,?,?,?,?,?,?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('sssssssss', $id, $nombre, $apellido, $ci, $emailEncriptado,
                    $usuarioAsignado, $contrasenaHash, $tipo, $estado);
            }
            $stmt->execute();
            $stmt->close();

            $this->saveSpecificUserData($conn, $usuario);

            if (!$outerTx) $conn->commit();
        } catch (\Exception $e) {
            if (!$outerTx) $conn->rollback();
            throw new \RuntimeException("Error al guardar usuario: " . $e->getMessage(), 0, $e);
        }

        // Invalidar caché
        $this->invalidateUser($usuario->getId()->value(), $usuario->getUsuarioAsignado(), $usuario->getEmail()->value(), $usuario->getTipo()->value());
    }

    private function saveSpecificUserData(\mysqli $conn, Usuario $usuario): void
    {
        $id = $usuario->getId()->value();

        if ($usuario instanceof Tecnico) {
            $checkStmt = $conn->prepare("SELECT ID_Tecnico FROM Tecnico WHERE ID_Tecnico=?");
            $checkStmt->bind_param('s', $id);
            $checkStmt->execute();
            $exists = $checkStmt->get_result()->num_rows > 0;
            $checkStmt->close();

            $esp = $usuario->getEspecialidad();
            $cnt = $usuario->getCantidadActividades();
            if ($exists) {
                $s = $conn->prepare("UPDATE Tecnico SET Especialidad=?,Cantidad_Actividades=? WHERE ID_Tecnico=?");
                $s->bind_param('sis', $esp, $cnt, $id);
            } else {
                $s = $conn->prepare("INSERT INTO Tecnico (ID_Tecnico,Especialidad,Cantidad_Actividades) VALUES (?,?,?)");
                $s->bind_param('ssi', $id, $esp, $cnt);
            }
            $s->execute(); $s->close();

        } elseif ($usuario instanceof Logistica) {
            $checkStmt = $conn->prepare("SELECT ID_Logistica FROM Logistica WHERE ID_Logistica=?");
            $checkStmt->bind_param('s', $id);
            $checkStmt->execute();
            $exists = $checkStmt->get_result()->num_rows > 0;
            $checkStmt->close();
            if (!$exists) {
                $s = $conn->prepare("INSERT INTO Logistica (ID_Logistica) VALUES (?)");
                $s->bind_param('s', $id);
                $s->execute(); $s->close();
            }
        }
    }

    // =========================================================================
    // LECTURA
    // =========================================================================

    public function searchById(Uuid $id): ?Usuario
    {
        $cacheKey = "usuario:id:{$id->value()}";

        return $this->cache->remember($cacheKey, function () use ($id) {
            $conn = $this->db->getConnection();
            $sql  = "SELECT u.*, t.Especialidad, t.Cantidad_Actividades 
                     FROM usuario u 
                     LEFT JOIN Tecnico t ON u.ID_Usuario = t.ID_Tecnico 
                     WHERE u.ID_Usuario = ?";
            $stmt = $conn->prepare($sql);
            $v    = $id->value();
            $stmt->bind_param('s', $v);
            $stmt->execute();
            $row  = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $row ? $this->hydrate($row) : null;
        }, $this->ttl);
    }

    public function findById(Uuid $id): ?Usuario
    {
        return $this->searchById($id);
    }

    public function searchByEmail(string $email): ?Usuario
    {
        $cacheKey = "usuario:email:" . md5($email);

        return $this->cache->remember($cacheKey, function () use ($email) {
            $conn = $this->db->getConnection();
            $sql  = "SELECT u.*, t.Especialidad, t.Cantidad_Actividades 
                     FROM usuario u 
                     LEFT JOIN Tecnico t ON u.ID_Usuario = t.ID_Tecnico 
                     WHERE u.email = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $row  = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $row ? $this->hydrate($row) : null;
        }, $this->ttl);
    }

    public function findByEmail(string $email): ?Usuario
    {
        return $this->searchByEmail($email);
    }

    public function searchByUsuarioAsignado(string $usuarioAsignado): ?Usuario
    {
        $cacheKey = "usuario:username:{$usuarioAsignado}";

        return $this->cache->remember($cacheKey, function () use ($usuarioAsignado) {
            $conn = $this->db->getConnection();
            $sql  = "SELECT u.*, t.Especialidad, t.Cantidad_Actividades 
                     FROM usuario u 
                     LEFT JOIN Tecnico t ON u.ID_Usuario = t.ID_Tecnico 
                     WHERE u.usuario_asignado = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('s', $usuarioAsignado);
            $stmt->execute();
            $row  = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if (!$row) {
                error_log("Usuario no encontrado con usuario_asignado: {$usuarioAsignado}");
                return null;
            }
            return $this->hydrate($row);
        }, $this->ttl);
    }

    public function findAll(array $filtros = []): array
    {
        $cacheKey = "usuarios:all:" . md5(serialize($filtros));

        return $this->cache->remember($cacheKey, function () use ($filtros) {
            $conn   = $this->db->getConnection();
            $sql    = "SELECT u.*, t.Especialidad, t.Cantidad_Actividades 
                       FROM usuario u 
                       LEFT JOIN Tecnico t ON u.ID_Usuario = t.ID_Tecnico 
                       WHERE 1=1";
            $params = [];
            $types  = "";

            if (!empty($filtros['tipo']))   { $sql .= " AND u.tipo=?";   $params[] = $filtros['tipo'];   $types .= "s"; }
            if (!empty($filtros['estado'])) { $sql .= " AND u.estado=?"; $params[] = $filtros['estado']; $types .= "s"; }
            if (!empty($filtros['ci']))     { $sql .= " AND u.ci=?";     $params[] = $filtros['ci'];     $types .= "s"; }

            $sql .= " ORDER BY u.nombre ASC";

            if (isset($filtros['limit']))  { $sql .= " LIMIT ?";  $params[] = (int)$filtros['limit'];  $types .= "i"; }
            if (isset($filtros['offset'])) { $sql .= " OFFSET ?"; $params[] = (int)$filtros['offset']; $types .= "i"; }

            $stmt = $conn->prepare($sql);
            if (!empty($params)) $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result   = $stmt->get_result();
            $usuarios = [];
            while ($row = $result->fetch_assoc()) $usuarios[] = $this->hydrate($row);
            $stmt->close();
            return $usuarios;
        }, $this->ttl);
    }

    public function findByTipo(string $tipo, ?Uuid $excluirId = null): array
    {
        $excluirStr = $excluirId ? $excluirId->value() : 'none';
        $cacheKey   = "usuarios:tipo:{$tipo}:excluir:{$excluirStr}";

        return $this->cache->remember($cacheKey, function () use ($tipo, $excluirId) {
            $conn   = $this->db->getConnection();
            $sql    = "SELECT u.*, t.Especialidad, t.Cantidad_Actividades 
                       FROM usuario u 
                       LEFT JOIN Tecnico t ON u.ID_Usuario = t.ID_Tecnico 
                       WHERE u.tipo=?";
            $params = [$tipo];
            $types  = "s";
            if ($excluirId) {
                $sql .= " AND u.ID_Usuario!=?";
                $params[] = $excluirId->value();
                $types  .= "s";
            }
            $sql .= " ORDER BY u.nombre ASC";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $usuarios = [];
            while ($row = $stmt->get_result()->fetch_assoc()) $usuarios[] = $this->hydrate($row);
            $stmt->close();
            return $usuarios;
        }, $this->ttl);
    }

    public function findTecnicosByEspecialidad(string $especialidad): array
    {
        $cacheKey = "tecnicos:especialidad:{$especialidad}";

        return $this->cache->remember($cacheKey, function () use ($especialidad) {
            $conn = $this->db->getConnection();
            $sql  = "SELECT u.*, t.Especialidad, t.Cantidad_Actividades 
                     FROM usuario u
                     INNER JOIN Tecnico t ON u.ID_Usuario = t.ID_Tecnico
                     WHERE t.Especialidad=? AND u.tipo='Tecnico'
                     ORDER BY u.nombre ASC";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('s', $especialidad);
            $stmt->execute();
            $tecnicos = [];
            while ($row = $stmt->get_result()->fetch_assoc()) $tecnicos[] = $this->hydrateTecnico($row);
            $stmt->close();
            return $tecnicos;
        }, $this->ttl);
    }

    // =========================================================================
    // EXISTS / COUNT (sin caché — consultas ligeras)
    // =========================================================================

    public function existsByEmail(string $emailEncriptado): bool
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM usuario WHERE email=?");
        $stmt->bind_param('s', $emailEncriptado);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row['total'] > 0;
    }

    public function existsByCi(string $ciEncriptada): bool
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM usuario WHERE ci=?");
        $stmt->bind_param('s', $ciEncriptada);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row['total'] > 0;
    }

    public function existsByUsuarioAsignado(string $usuarioAsignado): bool
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM usuario WHERE usuario_asignado=?");
        $stmt->bind_param('s', $usuarioAsignado);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row['total'] > 0;
    }

    public function existsByUsuarioAsignadoAndNotId(string $usuarioAsignado, Uuid $id): bool
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM usuario WHERE usuario_asignado=? AND ID_Usuario!=?");
        $v    = $id->value();
        $stmt->bind_param('ss', $usuarioAsignado, $v);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row['total'] > 0;
    }

    public function hasMachinesAssigned(Uuid $id): bool
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM MaquinaRecreativa 
                                WHERE ID_Tecnico_Ensamblador=? OR ID_Tecnico_Comprobador=? OR ID_Tecnico_Mantenimiento=?");
        $v    = $id->value();
        $stmt->bind_param('sss', $v, $v, $v);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row['total'] > 0;
    }

    // =========================================================================
    // DELETE
    // =========================================================================

    public function delete(Uuid $id): void
    {
        // Obtener datos antes de eliminar para invalidar caché correctamente
        $usuario = $this->searchById($id);

        $conn = $this->db->getConnection();
        try {
            $conn->begin_transaction();
            $idValue = $id->value();
            $tables  = [
                'comentario'                  => 'ID_Usuario_Emisor',
                'notificaciones'              => 'ID_Usuario',
                'NotificacionMaquinaRecreativa' => 'ID_Destinatario',
                'componente_usuario'          => 'ID_Usuario',
                'inicio_sesion'               => 'ID_Usuario',
                'historial_actividades'       => 'ID_Usuario',
                'Tecnico'                     => 'ID_Tecnico',
                'Logistica'                   => 'ID_Logistica',
            ];
            foreach ($tables as $table => $col) {
                $s = $conn->prepare("DELETE FROM {$table} WHERE {$col}=?");
                $s->bind_param('s', $idValue);
                $s->execute(); $s->close();
            }
            $s = $conn->prepare("DELETE FROM reporte WHERE ID_Usuario_Emisor=? OR ID_Usuario_Destinatario=?");
            $s->bind_param('ss', $idValue, $idValue);
            $s->execute(); $s->close();

            $s = $conn->prepare("DELETE FROM usuario WHERE ID_Usuario=?");
            $s->bind_param('s', $idValue);
            $s->execute(); $s->close();

            $conn->commit();
        } catch (\Exception $e) {
            $conn->rollback();
            throw new \RuntimeException("Error al eliminar usuario: " . $e->getMessage(), 0, $e);
        }

        // Invalidar caché
        if ($usuario) {
            $this->invalidateUser($usuario->getId()->value(), $usuario->getUsuarioAsignado(), $usuario->getEmail()->value(), $usuario->getTipo()->value());
        } else {
            $this->cache->delete("usuario:id:{$id->value()}");
        }
    }

    // =========================================================================
    // SESIÓN / HISTORIAL (no cacheados — operaciones puntuales)
    // =========================================================================

    public function registrarLogout(Uuid $id): void
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("UPDATE inicio_sesion SET fecha_ultima_sesion=NOW() 
                                WHERE ID_Usuario=? AND fecha_ultima_sesion IS NULL 
                                ORDER BY fecha_inicio DESC LIMIT 1");
        $v    = $id->value();
        $stmt->bind_param('s', $v);
        $stmt->execute(); $stmt->close();
    }

    public function registrarActividad(Uuid $id, string $descripcion): void
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("INSERT INTO historial_actividades (ID_Usuario,descripcion,fecha_registro) VALUES (?,?,NOW())");
        $v    = $id->value();
        $stmt->bind_param('ss', $v, $descripcion);
        $stmt->execute(); $stmt->close();
    }

    public function obtenerHistorialActividades(Uuid $id): array
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("SELECT * FROM historial_actividades WHERE ID_Usuario=? ORDER BY fecha_registro DESC LIMIT 50");
        $v    = $id->value();
        $stmt->bind_param('s', $v);
        $stmt->execute();
        $actividades = [];
        while ($row = $stmt->get_result()->fetch_assoc()) $actividades[] = $row;
        $stmt->close();
        return $actividades;
    }

    // =========================================================================
    // INVALIDACIÓN HELPER
    // =========================================================================

    private function invalidateUser(string $id, string $username, string $emailPlain, string $tipo): void
    {
        $this->cache->delete("usuario:id:{$id}");
        $this->cache->delete("usuario:username:{$username}");
        $this->cache->delete("usuario:email:" . md5($emailPlain));
        // Listas por tipo
        $this->cache->delete("usuarios:tipo:{$tipo}:excluir:none");
        $this->cache->delete("usuarios:tipo:{$tipo}:excluir:{$id}");
        // Lista genérica (patrón — si Redis disponible)
        if ($this->cache instanceof \maquinas_recreativas\Infrastructure\Cache\RedisCache) {
            $this->cache->deleteByPattern("usuarios:all:*");
            if ($tipo === 'Tecnico') {
                $this->cache->deleteByPattern("tecnicos:especialidad:*");
            }
        }
    }

    // =========================================================================
    // HYDRATE
    // =========================================================================

    public function hydrate(array $row): Usuario
    {
        $id         = new Uuid($row['ID_Usuario']);
        $emailValue = !empty($row['email']) ? CifradoHelper::desencriptar($row['email']) : '';
        if (empty($emailValue)) {
            error_log("AVISO: Usuario {$row['ID_Usuario']} tiene email vacío.");
            $emailValue = $row['usuario_asignado'] . '@temp.local';
        }
        $email  = new Email($emailValue);
        $tipo   = new TipoUsuario($row['tipo']);
        $estado = new EstadoUsuario($row['estado']);
        $ci     = !empty($row['ci']) ? CifradoHelper::desencriptar($row['ci']) : '';

        switch ($tipo->value()) {
            case TipoUsuario::TECNICO:
                $esp = $row['Especialidad'] ?? null;
                if (empty($esp)) {
                    error_log("AVISO: Técnico {$row['ID_Usuario']} sin fila en tabla Tecnico.");
                    return new Usuario($id, $row['nombre'], $row['apellido'], $ci, $email,
                        $row['usuario_asignado'], $row['contrasena'], $tipo, $estado);
                }
                return new Tecnico($id, $row['nombre'], $row['apellido'], $ci, $email,
                    $row['usuario_asignado'], $row['contrasena'], $estado,
                    $esp, (int)($row['Cantidad_Actividades'] ?? 0));

            case TipoUsuario::LOGISTICA:
                return new Logistica($id, $row['nombre'], $row['apellido'], $ci, $email,
                    $row['usuario_asignado'], $row['contrasena'], $estado);

            default:
                return new Usuario($id, $row['nombre'], $row['apellido'], $ci, $email,
                    $row['usuario_asignado'], $row['contrasena'], $tipo, $estado);
        }
    }

    private function hydrateTecnico(array $row): Tecnico
    {
        $id     = new Uuid($row['ID_Usuario']);
        $email  = new Email(CifradoHelper::desencriptar($row['email']));
        $estado = new EstadoUsuario($row['estado']);
        $ci     = !empty($row['ci']) ? CifradoHelper::desencriptar($row['ci']) : '';
        return new Tecnico($id, $row['nombre'], $row['apellido'], $ci, $email,
            $row['usuario_asignado'], $row['contrasena'], $estado,
            $row['Especialidad'] ?? '', (int)($row['Cantidad_Actividades'] ?? 0));
    }
}
