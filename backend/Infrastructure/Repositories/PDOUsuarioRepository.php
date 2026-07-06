<?php
/**
 * Infrastructure/Repositories/PDOUsuarioRepository.php
 * 
 * Versión con PDO para procedimientos almacenados
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
use PDO;

class PDOUsuarioRepository implements UsuarioRepository
{
    private Database $db;
    private CacheInterface $cache;
    private int $ttl = 1800;

    public function __construct(Database $db, ?CacheInterface $cache = null)
    {
        $this->db = $db;
        $this->cache = $cache ?? CacheFactory::create();
    }

    // =========================================================================
    // ESCRITURA - Usando SPs
    // =========================================================================

    public function save(Usuario $usuario): void
    {
        $conn = $this->db->getConnection();
        $id = $usuario->getId()->value();
        $nombre = $usuario->getNombre();
        $apellido = $usuario->getApellido();
        $ciEncriptada = CifradoHelper::encriptar($usuario->getCi());
        $emailEncriptado = CifradoHelper::encriptar($usuario->getEmail()->value());
        $username = $usuario->getUsuarioAsignado();
        $contrasenaHash = $usuario->getContrasenaHash();
        $tipo = $usuario->getTipo()->value();
        $estado = $usuario->getEstado()->value();

        try {
            $existing = $this->searchById($usuario->getId());

            if ($existing) {
                $stmt = $conn->prepare("CALL sp_actualizar_usuario(?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$id, $nombre, $apellido, $ciEncriptada, $emailEncriptado, $username, $contrasenaHash, $tipo, $estado]);
            } else {
                $stmt = $conn->prepare("CALL sp_insertar_usuario(?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$id, $nombre, $apellido, $ciEncriptada, $emailEncriptado, $username, $contrasenaHash, $tipo, $estado]);
            }
            $stmt->closeCursor();

            $this->saveSpecificUserData($conn, $usuario);

        } catch (\Exception $e) {
            throw new \RuntimeException("Error al guardar usuario: " . $e->getMessage(), 0, $e);
        }

        $this->invalidateUser($id, $username, $usuario->getEmail()->value(), $tipo);
    }

    private function saveSpecificUserData(\PDO $conn, Usuario $usuario): void
    {
        $id = $usuario->getId()->value();

        if ($usuario instanceof Tecnico) {
            $esp = $usuario->getEspecialidad();
            $cnt = $usuario->getCantidadActividades();
            $stmt = $conn->prepare("CALL sp_insertar_tecnico(?, ?, ?)");
            $stmt->execute([$id, $esp, $cnt]);
            $stmt->closeCursor();

        } elseif ($usuario instanceof Logistica) {
            $check = $conn->prepare("SELECT ID_Logistica FROM Logistica WHERE ID_Logistica = ?");
            $check->execute([$id]);
            $exists = $check->fetchColumn() > 0;
            $check->closeCursor();

            if (!$exists) {
                $stmt = $conn->prepare("INSERT INTO Logistica (ID_Logistica) VALUES (?)");
                $stmt->execute([$id]);
                $stmt->closeCursor();
            }
        }
    }

    public function delete(Uuid $id): void
    {
        $usuario = $this->searchById($id);
        $conn = $this->db->getConnection();

        $stmt = $conn->prepare("CALL sp_eliminar_usuario(?)");
        $stmt->execute([$id->value()]);
        $stmt->closeCursor();

        if ($usuario) {
            $this->invalidateUser(
                $usuario->getId()->value(),
                $usuario->getUsuarioAsignado(),
                $usuario->getEmail()->value(),
                $usuario->getTipo()->value()
            );
        } else {
            $this->cache->delete("usuario:id:{$id->value()}");
        }
    }

    // =========================================================================
    // LECTURA - Usando SPs
    // =========================================================================

    public function searchById(Uuid $id): ?Usuario
    {
        $cacheKey = "usuario:id:{$id->value()}";

        return $this->cache->remember($cacheKey, function () use ($id) {
            $conn = $this->db->getConnection();
            $stmt = $conn->prepare("CALL sp_buscar_usuario_por_id(?)");
            $stmt->execute([$id->value()]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            $this->db->clearPendingResults();
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
            $stmt = $conn->prepare("CALL sp_buscar_usuario_por_email(?)");
            $stmt->execute([$email]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            $this->db->clearPendingResults();
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
            $stmt = $conn->prepare("CALL sp_buscar_usuario_por_username(?)");
            $stmt->execute([$usuarioAsignado]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            $this->db->clearPendingResults();

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
        $conn = $this->db->getConnection();

        $tipo = $filtros['tipo'] ?? null;
        $estado = $filtros['estado'] ?? null;
        $ci = isset($filtros['ci']) ? CifradoHelper::encriptar($filtros['ci']) : null;
        $limit = (int)($filtros['limit'] ?? 100);
        $offset = (int)($filtros['offset'] ?? 0);

        $stmt = $conn->prepare("CALL sp_listar_usuarios(?, ?, ?, ?, ?)");
        $stmt->execute([$tipo, $estado, $ci, $limit, $offset]);

        $usuarios = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // NO desencriptar aquí: hydrate() ya lo hace
            $usuarios[] = $this->hydrate($row);
        }
        $stmt->closeCursor();
        $this->db->clearPendingResults();

        return $usuarios;
    }, $this->ttl);
}

    public function findByTipo(string $tipo, ?Uuid $excluirId = null): array
{
    $excluirStr = $excluirId ? $excluirId->value() : null;
    $cacheKey = "usuarios:tipo:{$tipo}:excluir:" . ($excluirStr ?? 'none');

    return $this->cache->remember($cacheKey, function () use ($tipo, $excluirStr) {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("CALL sp_listar_usuarios_por_tipo(?, ?)");
        $stmt->execute([$tipo, $excluirStr]);

        $usuarios = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // NO desencriptar aquí tampoco
            $usuarios[] = $this->hydrate($row);
        }
        $stmt->closeCursor();
        $this->db->clearPendingResults();

        return $usuarios;
    }, $this->ttl);
}

    public function findTecnicosByEspecialidad(string $especialidad): array
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("CALL sp_tecnicos_por_especialidad(?)");
        $stmt->execute([$especialidad]);
        
        $tecnicos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        $this->db->clearPendingResults();
        
        return $tecnicos;
    }

    // =========================================================================
    // EXISTS / COUNT - Usando SPs con OUT parameters
    // =========================================================================

    public function existsByEmail(string $emailEncriptado): bool
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("CALL sp_existe_email(?, @existe)");
        $stmt->execute([$emailEncriptado]);
        $stmt->closeCursor();
        
        $result = $conn->query("SELECT @existe as existe");
        $row = $result->fetch(PDO::FETCH_ASSOC);
        $result->closeCursor();
        $this->db->clearPendingResults();
        
        return (bool)($row['existe'] ?? 0);
    }

    public function existsByCi(string $ciEncriptada): bool
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("CALL sp_existe_ci(?, @existe)");
        $stmt->execute([$ciEncriptada]);
        $stmt->closeCursor();
        
        $result = $conn->query("SELECT @existe as existe");
        $row = $result->fetch(PDO::FETCH_ASSOC);
        $result->closeCursor();
        $this->db->clearPendingResults();
        
        return (bool)($row['existe'] ?? 0);
    }

    public function existsByUsuarioAsignado(string $usuarioAsignado): bool
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("CALL sp_existe_username(?, @existe)");
        $stmt->execute([$usuarioAsignado]);
        $stmt->closeCursor();
        
        $result = $conn->query("SELECT @existe as existe");
        $row = $result->fetch(PDO::FETCH_ASSOC);
        $result->closeCursor();
        $this->db->clearPendingResults();
        
        return (bool)($row['existe'] ?? 0);
    }

    public function existsByUsuarioAsignadoAndNotId(string $usuarioAsignado, Uuid $id): bool
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("CALL sp_existe_username_excluyendo_id(?, ?, @existe)");
        $stmt->execute([$usuarioAsignado, $id->value()]);
        $stmt->closeCursor();
        
        $result = $conn->query("SELECT @existe as existe");
        $row = $result->fetch(PDO::FETCH_ASSOC);
        $result->closeCursor();
        $this->db->clearPendingResults();
        
        return (bool)($row['existe'] ?? 0);
    }

    public function hasMachinesAssigned(Uuid $id): bool
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("CALL sp_usuario_tiene_maquinas(?, @tiene)");
        $stmt->execute([$id->value()]);
        $stmt->closeCursor();
        
        $result = $conn->query("SELECT @tiene as tiene");
        $row = $result->fetch(PDO::FETCH_ASSOC);
        $result->closeCursor();
        $this->db->clearPendingResults();
        
        return (bool)($row['tiene'] ?? 0);
    }

    // =========================================================================
    // SESIÓN / HISTORIAL - Usando SPs
    // =========================================================================

    public function registrarLogout(Uuid $id): void
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("CALL sp_registrar_logout(?)");
        $stmt->execute([$id->value()]);
        $stmt->closeCursor();
        $this->db->clearPendingResults();
    }

    public function registrarActividad(Uuid $id, string $descripcion): void
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("CALL sp_registrar_actividad(?, ?)");
        $stmt->execute([$id->value(), $descripcion]);
        $stmt->closeCursor();
        $this->db->clearPendingResults();
    }

    public function obtenerHistorialActividades(Uuid $id): array
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("CALL sp_obtener_historial_actividades(?, 50)");
        $stmt->execute([$id->value()]);
        
        $actividades = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        $this->db->clearPendingResults();
        
        return $actividades;
    }

    // =========================================================================
    // MÉTODOS ADICIONALES PARA ADMINISTRADOR
    // =========================================================================

    public function getEstadisticas(): array
    {
        $cacheKey = "admin:estadisticas";

        return $this->cache->remember($cacheKey, function () {
            $conn = $this->db->getConnection();
            $stmt = $conn->prepare("CALL sp_estadisticas_usuarios()");
            $stmt->execute();
            
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            $this->db->clearPendingResults();
            
            return [
                'total' => (int)($data['total'] ?? 0),
                'por_tipo' => [
                    'Administrador' => (int)($data['administradores'] ?? 0),
                    'Tecnico' => (int)($data['tecnicos'] ?? 0),
                    'Logistica' => (int)($data['logistica'] ?? 0),
                    'Contabilidad' => (int)($data['contabilidad'] ?? 0),
                    'Usuario' => (int)($data['usuarios_normales'] ?? 0),
                ],
                'por_estado' => [
                    'Activo' => (int)($data['activos'] ?? 0),
                    'Inhabilitado' => (int)($data['inhabilitados'] ?? 0),
                    'Pendiente de asignacion' => (int)($data['pendientes'] ?? 0),
                ]
            ];
        }, 300);
    }

    public function updateEstado(Uuid $usuarioId, string $nuevoEstado): bool
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("CALL sp_cambiar_estado_usuario(?, ?)");
        $result = $stmt->execute([$usuarioId->value(), $nuevoEstado]);
        $stmt->closeCursor();
        $this->db->clearPendingResults();

        $v = $usuarioId->value();
        $this->cache->delete("usuario:id:{$v}");
        $this->cache->delete("admin:estadisticas");
        if ($this->cache instanceof \maquinas_recreativas\Infrastructure\Cache\RedisCache) {
            $this->cache->deleteByPattern("admin:usuarios:filters:*");
            $this->cache->deleteByPattern("usuarios:all:*");
            $this->cache->deleteByPattern("usuarios:tipo:*");
        }

        return $result;
    }

    public function cambiarContrasena(Uuid $usuarioId, string $nuevaContrasena): bool
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("CALL sp_cambiar_contrasena_usuario(?, ?)");
        $result = $stmt->execute([$usuarioId->value(), $nuevaContrasena]);
        $stmt->closeCursor();
        $this->db->clearPendingResults();

        $this->cache->delete("usuario:id:{$usuarioId->value()}");

        return $result;
    }

    public function actualizarUsername(Uuid $usuarioId, string $nuevoUsername): bool
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("CALL sp_actualizar_username(?, ?)");
        $result = $stmt->execute([$usuarioId->value(), $nuevoUsername]);
        $stmt->closeCursor();
        $this->db->clearPendingResults();

        $v = $usuarioId->value();
        $this->cache->delete("usuario:id:{$v}");
        $this->cache->delete("usuario:username:{$nuevoUsername}");

        return $result;
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
        $this->cache->delete("admin:estadisticas");
        
        if ($this->cache instanceof \maquinas_recreativas\Infrastructure\Cache\RedisCache) {
            $this->cache->deleteByPattern("usuarios:all:*");
            if ($tipo === 'Tecnico') {
                $this->cache->deleteByPattern("tecnicos:especialidad:*");
            }
            $this->cache->deleteByPattern("admin:usuarios:filters:*");
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
        $email = new Email($emailRaw);

        $tipo = new TipoUsuario($row['tipo']);
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