<?php
/**
 * Infrastructure/Repositories/MySQLAdministradorRepository.php
 *
 * TTL: admin:usuarios:filters → 1800 s  |  admin:estadisticas → 300 s
 * Invalida: writeAdminCaches() llamado desde AdministradorController.
 */
namespace maquinas_recreativas\Infrastructure\Repositories;

use maquinas_recreativas\Domain\Usuario\AdministradorRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Infrastructure\Database\Database;
use maquinas_recreativas\Infrastructure\Security\CifradoHelper;
use maquinas_recreativas\Infrastructure\Cache\CacheInterface;
use maquinas_recreativas\Infrastructure\Cache\CacheFactory;

class MySQLAdministradorRepository extends MySQLUsuarioRepository implements AdministradorRepository
{
    private Database       $db;
    private CacheInterface $cache;

    public function __construct(Database $db, ?CacheInterface $cache = null)
    {
        parent::__construct($db, $cache);
        $this->db    = $db;
        $this->cache = $cache ?? CacheFactory::create();
    }

    public function findAllWithFilters(array $filters = []): array
    {
        $cacheKey = "admin:usuarios:filters:" . md5(serialize($filters));

        return $this->cache->remember($cacheKey, function () use ($filters) {
            $conn   = $this->db->getConnection();
            $sql    = "SELECT u.*, t.Especialidad, t.Cantidad_Actividades 
                       FROM usuario u 
                       LEFT JOIN Tecnico t ON u.ID_Usuario = t.ID_Tecnico 
                       WHERE 1=1";
            $params = [];
            $types  = "";

            if (!empty($filters['tipo']))   { $sql .= " AND u.tipo=?";   $params[] = $filters['tipo'];                              $types .= "s"; }
            if (!empty($filters['estado'])) { $sql .= " AND u.estado=?"; $params[] = $filters['estado'];                            $types .= "s"; }
            if (!empty($filters['ci']))     { $sql .= " AND u.ci=?";     $params[] = CifradoHelper::encriptar($filters['ci']);       $types .= "s"; }

            $sql .= " ORDER BY u.nombre ASC";
            if (isset($filters['limit']))   { $sql .= " LIMIT ?";  $params[] = (int)$filters['limit'];  $types .= "i"; }
            if (isset($filters['offset']))  { $sql .= " OFFSET ?"; $params[] = (int)$filters['offset']; $types .= "i"; }

            $stmt = $conn->prepare($sql);
            if (!empty($params)) $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
            $rows   = [];
            while ($row = $result->fetch_assoc()) {
                if (!empty($row['email'])) $row['email'] = CifradoHelper::desencriptar($row['email']);
                if (!empty($row['ci']))    $row['ci']    = CifradoHelper::desencriptar($row['ci']);
                $rows[] = $row;
            }
            $stmt->close();
            return $rows;
        }, 1800);
    }

    public function getEstadisticas(): array
    {
        $cacheKey = "admin:estadisticas";

        return $this->cache->remember($cacheKey, function () {
            $conn = $this->db->getConnection();

            $res   = $conn->query("SELECT COUNT(*) as total FROM usuario");
            $total = $res->fetch_assoc()['total'];

            $res     = $conn->query("SELECT tipo, COUNT(*) as cantidad FROM usuario GROUP BY tipo");
            $porTipo = [];
            while ($row = $res->fetch_assoc()) $porTipo[$row['tipo']] = (int)$row['cantidad'];

            $res       = $conn->query("SELECT estado, COUNT(*) as cantidad FROM usuario GROUP BY estado");
            $porEstado = [];
            while ($row = $res->fetch_assoc()) $porEstado[$row['estado']] = (int)$row['cantidad'];

            return ['total' => (int)$total, 'por_tipo' => $porTipo, 'por_estado' => $porEstado];
        }, 300);
    }

    public function updateEstado(Uuid $usuarioId, string $nuevoEstado): bool
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("UPDATE usuario SET estado=? WHERE ID_Usuario=?");
        $v    = $usuarioId->value();
        $stmt->bind_param('ss', $nuevoEstado, $v);
        $result = $stmt->execute();
        $stmt->close();

        // Invalidar caché del usuario y estadísticas
        $this->cache->delete("usuario:id:{$v}");
        $this->invalidateAdminCaches();
        return $result;
    }

    public function buscarPorNombre(string $termino, int $limit = 10): array
    {
        // No se cachea — búsqueda dinámica
        $conn    = $this->db->getConnection();
        $like    = "%{$termino}%";
        $sql     = "SELECT u.*, t.Especialidad, t.Cantidad_Actividades 
                    FROM usuario u 
                    LEFT JOIN Tecnico t ON u.ID_Usuario = t.ID_Tecnico 
                    WHERE u.nombre LIKE ? OR u.apellido LIKE ?
                    ORDER BY u.nombre ASC LIMIT ?";
        $stmt    = $conn->prepare($sql);
        $stmt->bind_param('ssi', $like, $like, $limit);
        $stmt->execute();
        $rows = [];
        while ($row = $stmt->get_result()->fetch_assoc()) {
            if (!empty($row['email'])) $row['email'] = CifradoHelper::desencriptar($row['email']);
            if (!empty($row['ci']))    $row['ci']    = CifradoHelper::desencriptar($row['ci']);
            $rows[] = $row;
        }
        $stmt->close();
        return $rows;
    }

    /** Llama a esto desde el Controller tras create/update/delete/cambiarEstado */
    public function invalidateAdminCaches(): void
    {
        $this->cache->delete("admin:estadisticas");
        if ($this->cache instanceof \maquinas_recreativas\Infrastructure\Cache\RedisCache) {
            $this->cache->deleteByPattern("admin:usuarios:filters:*");
            $this->cache->deleteByPattern("usuarios:all:*");
            $this->cache->deleteByPattern("usuarios:tipo:*");
        }
    }
}
