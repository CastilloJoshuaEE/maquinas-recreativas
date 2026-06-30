<?php
/**
 * Infrastructure/Repositories/MySQLAdministradorRepository.php
 * Migrado a PDO. TTL: filters 1800s, estadisticas 300s.
 */
namespace maquinas_recreativas\Infrastructure\Repositories;

use PDO;
use maquinas_recreativas\Domain\Usuario\AdministradorRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Infrastructure\Database\Database;
use maquinas_recreativas\Infrastructure\Security\CifradoHelper;
use maquinas_recreativas\Infrastructure\Cache\CacheInterface;
use maquinas_recreativas\Infrastructure\Cache\CacheFactory;

class MySQLAdministradorRepository extends MySQLUsuarioRepository implements AdministradorRepository
{
    private Database       $dbAdmin;
    private CacheInterface $cacheAdmin;

    public function __construct(Database $db, ?CacheInterface $cache = null)
    {
        parent::__construct($db, $cache);
        $this->dbAdmin    = $db;
        $this->cacheAdmin = $cache ?? CacheFactory::create();
    }

    public function findAllWithFilters(array $filters = []): array
    {
        $cacheKey = "admin:usuarios:filters:" . md5(serialize($filters));

        return $this->cacheAdmin->remember($cacheKey, function () use ($filters) {
            $conn   = $this->dbAdmin->getConnection();
            $sql    = "SELECT u.*, t.Especialidad, t.Cantidad_Actividades
                       FROM usuario u
                       LEFT JOIN Tecnico t ON u.ID_Usuario = t.ID_Tecnico
                       WHERE 1=1";
            $params = [];

            if (!empty($filters['tipo']))   { $sql .= " AND u.tipo=?";   $params[] = $filters['tipo']; }
            if (!empty($filters['estado'])) { $sql .= " AND u.estado=?"; $params[] = $filters['estado']; }
            if (!empty($filters['ci']))     { $sql .= " AND u.ci=?";     $params[] = CifradoHelper::encriptar($filters['ci']); }

            $sql .= " ORDER BY u.nombre ASC";
            if (isset($filters['limit']))   { $sql .= " LIMIT ?";  $params[] = (int)$filters['limit']; }
            if (isset($filters['offset']))  { $sql .= " OFFSET ?"; $params[] = (int)$filters['offset']; }

            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            $rows = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if (!empty($row['email'])) $row['email'] = CifradoHelper::desencriptar($row['email']);
                if (!empty($row['ci']))    $row['ci']    = CifradoHelper::desencriptar($row['ci']);
                $rows[] = $row;
            }
            return $rows;
        }, 1800);
    }

    public function getEstadisticas(): array
    {
        $cacheKey = "admin:estadisticas";

        return $this->cacheAdmin->remember($cacheKey, function () {
            $conn = $this->dbAdmin->getConnection();

            $res   = $conn->query("SELECT COUNT(*) as total FROM usuario");
            $total = $res->fetch(PDO::FETCH_ASSOC)['total'];

            $res     = $conn->query("SELECT tipo, COUNT(*) as cantidad FROM usuario GROUP BY tipo");
            $porTipo = [];
            while ($row = $res->fetch(PDO::FETCH_ASSOC)) $porTipo[$row['tipo']] = (int)$row['cantidad'];

            $res       = $conn->query("SELECT estado, COUNT(*) as cantidad FROM usuario GROUP BY estado");
            $porEstado = [];
            while ($row = $res->fetch(PDO::FETCH_ASSOC)) $porEstado[$row['estado']] = (int)$row['cantidad'];

            return ['total' => (int)$total, 'por_tipo' => $porTipo, 'por_estado' => $porEstado];
        }, 300);
    }

    public function updateEstado(Uuid $usuarioId, string $nuevoEstado): bool
    {
        $conn = $this->dbAdmin->getConnection();
        $stmt = $conn->prepare("UPDATE usuario SET estado=? WHERE ID_Usuario=?");
        $v    = $usuarioId->value();
        $result = $stmt->execute([$nuevoEstado, $v]);

        $this->cacheAdmin->delete("usuario:id:{$v}");
        $this->invalidateAdminCaches();
        return $result;
    }

    public function buscarPorNombre(string $termino, int $limit = 10): array
    {
        $conn    = $this->dbAdmin->getConnection();
        $like    = "%{$termino}%";
        $sql     = "SELECT u.*, t.Especialidad, t.Cantidad_Actividades
                    FROM usuario u
                    LEFT JOIN Tecnico t ON u.ID_Usuario = t.ID_Tecnico
                    WHERE u.nombre LIKE ? OR u.apellido LIKE ?
                    ORDER BY u.nombre ASC LIMIT ?";
        $stmt    = $conn->prepare($sql);
        $stmt->execute([$like, $like, $limit]);
        $rows = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if (!empty($row['email'])) $row['email'] = CifradoHelper::desencriptar($row['email']);
            if (!empty($row['ci']))    $row['ci']    = CifradoHelper::desencriptar($row['ci']);
            $rows[] = $row;
        }
        return $rows;
    }

    public function invalidateAdminCaches(): void
    {
        $this->cacheAdmin->delete("admin:estadisticas");
        if ($this->cacheAdmin instanceof \maquinas_recreativas\Infrastructure\Cache\RedisCache) {
            $this->cacheAdmin->deleteByPattern("admin:usuarios:filters:*");
            $this->cacheAdmin->deleteByPattern("usuarios:all:*");
            $this->cacheAdmin->deleteByPattern("usuarios:tipo:*");
        }
    }
}