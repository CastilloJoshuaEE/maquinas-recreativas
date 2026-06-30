<?php
/**
 * Infrastructure/Repositories/MySQLUsuarioRepository.php
 * Migrado de mysqli a PDO -> funciona igual en MySQL y PostgreSQL.
 * TTL caché: 1800 s.
 */
declare(strict_types=1);

namespace maquinas_recreativas\Infrastructure\Repositories;

use PDO;
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
    protected Database       $db;
    protected CacheInterface $cache;
    protected int            $ttl = 1800;

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
        $outerTx = $conn->inTransaction();

        try {
            if (!$outerTx) $conn->beginTransaction();

            $existing = $this->searchById($usuario->getId());
            $nombre   = $usuario->getNombre();
            $apellido = $usuario->getApellido();
            $ciEncriptada     = CifradoHelper::encriptar($usuario->getCi());
            $emailEncriptado  = CifradoHelper::encriptar($usuario->getEmail()->value());
            $usuarioAsignado  = $usuario->getUsuarioAsignado();
            $contrasenaHash   = $usuario->getContrasenaHash();
            $tipo             = $usuario->getTipo()->value();
            $estado           = $usuario->getEstado()->value();
            $id               = $usuario->getId()->value();

            if ($existing) {
                $sql = "UPDATE usuario SET
                            nombre=?,apellido=?,ci=?,email=?,
                            usuario_asignado=?,contrasena=?,tipo=?,estado=?
                        WHERE ID_Usuario=?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$nombre, $apellido, $ciEncriptada, $emailEncriptado,
                    $usuarioAsignado, $contrasenaHash, $tipo, $estado, $id]);
            } else {
                $sql = "INSERT INTO usuario (ID_Usuario,nombre,apellido,ci,email,usuario_asignado,contrasena,tipo,estado)
                        VALUES (?,?,?,?,?,?,?,?,?)";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$id, $nombre, $apellido, $ciEncriptada, $emailEncriptado,
                    $usuarioAsignado, $contrasenaHash, $tipo, $estado]);
            }

            $this->saveSpecificUserData($conn, $usuario);

            if (!$outerTx) $conn->commit();
        } catch (\Exception $e) {
            if (!$outerTx && $conn->inTransaction()) $conn->rollBack();
            throw new \RuntimeException("Error al guardar usuario: " . $e->getMessage(), 0, $e);
        }

        $this->invalidateUser($usuario->getId()->value(), $usuario->getUsuarioAsignado(), $usuario->getEmail()->value(), $usuario->getTipo()->value());
    }

    private function saveSpecificUserData(PDO $conn, Usuario $usuario): void
    {
        $id = $usuario->getId()->value();

        if ($usuario instanceof Tecnico) {
            $checkStmt = $conn->prepare("SELECT ID_Tecnico FROM Tecnico WHERE ID_Tecnico=?");
            $checkStmt->execute([$id]);
            $exists = (bool) $checkStmt->fetch(PDO::FETCH_ASSOC);

            $esp = $usuario->getEspecialidad();
            $cnt = $usuario->getCantidadActividades();
            if ($exists) {
                $s = $conn->prepare("UPDATE Tecnico SET Especialidad=?,Cantidad_Actividades=? WHERE ID_Tecnico=?");
                $s->execute([$esp, $cnt, $id]);
            } else {
                $s = $conn->prepare("INSERT INTO Tecnico (ID_Tecnico,Especialidad,Cantidad_Actividades) VALUES (?,?,?)");
                $s->execute([$id, $esp, $cnt]);
            }
        } elseif ($usuario instanceof Logistica) {
            $checkStmt = $conn->prepare("SELECT ID_Logistica FROM Logistica WHERE ID_Logistica=?");
            $checkStmt->execute([$id]);
            $exists = (bool) $checkStmt->fetch(PDO::FETCH_ASSOC);
            if (!$exists) {
                $s = $conn->prepare("INSERT INTO Logistica (ID_Logistica) VALUES (?)");
                $s->execute([$id]);
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
            $stmt->execute([$id->value()]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ? $this->hydrate($row) : null;
        }, $this->ttl);
    }

    public function findById(Uuid $id): ?Usuario { return $this->searchById($id); }

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
            $stmt->execute([$email]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ? $this->hydrate($row) : null;
        }, $this->ttl);
    }

    public function findByEmail(string $email): ?Usuario { return $this->searchByEmail($email); }

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
            $stmt->execute([$usuarioAsignado]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
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

            if (!empty($filtros['tipo']))   { $sql .= " AND u.tipo=?";   $params[] = $filtros['tipo']; }
            if (!empty($filtros['estado'])) { $sql .= " AND u.estado=?"; $params[] = $filtros['estado']; }
            if (!empty($filtros['ci']))     { $sql .= " AND u.ci=?";     $params[] = $filtros['ci']; }

            $sql .= " ORDER BY u.nombre ASC";

            if (isset($filtros['limit']))  { $sql .= " LIMIT ?";  $params[] = (int)$filtros['limit']; }
            if (isset($filtros['offset'])) { $sql .= " OFFSET ?"; $params[] = (int)$filtros['offset']; }

            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            $usuarios = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) $usuarios[] = $this->hydrate($row);
            return $usuarios;
        }, $this->ttl);
    }

    public function findByTipo(string $tipo, ?Uuid $excluirId = null): array
    {
        $excluirStr = $excluirId ? $excluirId->value() : 'none';
        $cacheKey = "usuarios:tipo:{$tipo}:excluir:{$excluirStr}";

        return $this->cache->remember($cacheKey, function () use ($tipo, $excluirId) {
            $conn = $this->db->getConnection();
            $sql = "SELECT u.*, t.Especialidad, t.Cantidad_Actividades
                    FROM usuario u
                    LEFT JOIN Tecnico t ON u.ID_Usuario = t.ID_Tecnico
                    WHERE u.tipo = ?";
            $params = [$tipo];
            if ($excluirId) {
                $sql .= " AND u.ID_Usuario != ?";
                $params[] = $excluirId->value();
            }
            $sql .= " ORDER BY u.nombre ASC";
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            $usuarios = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $usuarios[] = $this->hydrate($row);
            }
            return $usuarios;
        }, $this->ttl);
    }

    public function findTecnicosByEspecialidad(string $especialidad): array
    {
        $conn = $this->db->getConnection();
        $sql = "SELECT
                    u.ID_Usuario        AS id,
                    u.nombre,
                    u.apellido,
                    u.usuario_asignado,
                    u.tipo,
                    u.estado,
                    t.Especialidad      AS especialidad,
                    t.Cantidad_Actividades AS cantidad_actividades
                FROM usuario u
                INNER JOIN Tecnico t ON u.ID_Usuario = t.ID_Tecnico
                WHERE t.Especialidad = ?
                  AND u.estado = 'Activo'
                ORDER BY t.Cantidad_Actividades ASC, u.nombre ASC";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            error_log("findTecnicosByEspecialidad prepare error");
            return [];
        }

        $stmt->execute([$especialidad]);
        $tecnicos = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $cleanRow = [];
            foreach ($row as $key => $value) {
                if ($value === null) {
                    $cleanRow[$key] = '';
                } elseif (is_string($value)) {
                    $clean = preg_replace('/[\x00-\x1F\x7F]/u', '', $value);
                    if (!mb_check_encoding($clean, 'UTF-8')) {
                        $clean = mb_convert_encoding($clean, 'UTF-8', 'UTF-8');
                    }
                    $cleanRow[$key] = $clean;
                } else {
                    $cleanRow[$key] = $value;
                }
            }
            $tecnicos[] = $cleanRow;
        }

        error_log("findTecnicosByEspecialidad({$especialidad}): " . count($tecnicos) . " encontrados (limpiados)");
        return $tecnicos;
    }

    // =========================================================================
    // EXISTS / COUNT
    // =========================================================================

    public function existsByEmail(string $emailEncriptado): bool
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM usuario WHERE email=?");
        $stmt->execute([$emailEncriptado]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'] > 0;
    }

    public function existsByCi(string $ciEncriptada): bool
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM usuario WHERE ci=?");
        $stmt->execute([$ciEncriptada]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'] > 0;
    }

    public function existsByUsuarioAsignado(string $usuarioAsignado): bool
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM usuario WHERE usuario_asignado=?");
        $stmt->execute([$usuarioAsignado]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'] > 0;
    }

    public function existsByUsuarioAsignadoAndNotId(string $usuarioAsignado, Uuid $id): bool
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM usuario WHERE usuario_asignado=? AND ID_Usuario!=?");
        $stmt->execute([$usuarioAsignado, $id->value()]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'] > 0;
    }

    public function hasMachinesAssigned(Uuid $id): bool
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM MaquinaRecreativa
                                WHERE ID_Tecnico_Ensamblador=? OR ID_Tecnico_Comprobador=? OR ID_Tecnico_Mantenimiento=?");
        $v = $id->value();
        $stmt->execute([$v, $v, $v]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'] > 0;
    }

    // =========================================================================
    // DELETE
    // =========================================================================

    public function delete(Uuid $id): void
    {
        $usuario = $this->searchById($id);
        $conn = $this->db->getConnection();
        try {
            $conn->beginTransaction();
            $idValue = $id->value();
            $tables  = [
                'comentario'                     => 'ID_Usuario_Emisor',
                'notificaciones'                 => 'ID_Usuario',
                'NotificacionMaquinaRecreativa'  => 'ID_Destinatario',
                'componente_usuario'             => 'ID_Usuario',
                'inicio_sesion'                  => 'ID_Usuario',
                'historial_actividades'          => 'ID_Usuario',
                'Tecnico'                        => 'ID_Tecnico',
                'Logistica'                      => 'ID_Logistica',
            ];
            foreach ($tables as $table => $col) {
                $s = $conn->prepare("DELETE FROM {$table} WHERE {$col}=?");
                $s->execute([$idValue]);
            }
            $s = $conn->prepare("DELETE FROM reporte WHERE ID_Usuario_Emisor=? OR ID_Usuario_Destinatario=?");
            $s->execute([$idValue, $idValue]);

            $s = $conn->prepare("DELETE FROM usuario WHERE ID_Usuario=?");
            $s->execute([$idValue]);

            $conn->commit();
        } catch (\Exception $e) {
            if ($conn->inTransaction()) $conn->rollBack();
            throw new \RuntimeException("Error al eliminar usuario: " . $e->getMessage(), 0, $e);
        }

        if ($usuario) {
            $this->invalidateUser($usuario->getId()->value(), $usuario->getUsuarioAsignado(), $usuario->getEmail()->value(), $usuario->getTipo()->value());
        } else {
            $this->cache->delete("usuario:id:{$id->value()}");
        }
    }

    // =========================================================================
    // SESIÓN / HISTORIAL
    // =========================================================================

    public function registrarLogout(Uuid $id): void
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("UPDATE inicio_sesion SET fecha_ultima_sesion=NOW()
                                WHERE ID_Usuario=? AND fecha_ultima_sesion IS NULL
                                ORDER BY fecha_inicio DESC LIMIT 1");
        $stmt->execute([$id->value()]);
    }

    public function registrarActividad(Uuid $id, string $descripcion): void
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("INSERT INTO historial_actividades (ID_Usuario,descripcion,fecha_registro) VALUES (?,?,NOW())");
        $stmt->execute([$id->value(), $descripcion]);
    }

    public function obtenerHistorialActividades(Uuid $id): array
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("SELECT * FROM historial_actividades WHERE ID_Usuario=? ORDER BY fecha_registro DESC LIMIT 50");
        $stmt->execute([$id->value()]);
        $actividades = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $actividades[] = $row;
        }
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
        $this->cache->delete("usuarios:tipo:{$tipo}:excluir:none");
        $this->cache->delete("usuarios:tipo:{$tipo}:excluir:{$id}");
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
        $id = new Uuid($row['ID_Usuario']);

        $emailRaw = !empty($row['email']) ? CifradoHelper::desencriptar($row['email']) : '';
        if (empty($emailRaw) || $emailRaw === false) {
            $emailRaw = $row['usuario_asignado'] . '@temp.local';
        }
        if (!mb_check_encoding($emailRaw, 'UTF-8')) {
            $emailRaw = $row['usuario_asignado'] . '@temp.local';
        }
        $email = new Email($emailRaw);

        $tipo   = new TipoUsuario($row['tipo']);
        $estado = new EstadoUsuario($row['estado']);

        $ciDecrypted = !empty($row['ci']) ? CifradoHelper::desencriptar($row['ci']) : '';
        if (empty($ciDecrypted) || $ciDecrypted === false || strlen($ciDecrypted) < 6) {
            error_log("ADVERTENCIA: No se pudo desencriptar CI para usuario {$row['ID_Usuario']}");
            $ciDecrypted = '***ENCRIPTADO***';
        }
        $ci = $ciDecrypted;

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
}