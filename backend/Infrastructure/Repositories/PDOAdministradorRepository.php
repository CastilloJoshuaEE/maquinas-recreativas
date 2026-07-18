<?php
/**
 * Infrastructure/Repositories/PDOAdministradorRepository.php
 * 
 * Extiende PDOUsuarioRepository y usa sus SPs
 */
namespace maquinas_recreativas\Infrastructure\Repositories;

use maquinas_recreativas\Domain\Usuario\AdministradorRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Infrastructure\Database\Database;
use maquinas_recreativas\Infrastructure\Security\CifradoHelper;
use maquinas_recreativas\Infrastructure\Cache\CacheInterface;
use maquinas_recreativas\Infrastructure\Cache\CacheFactory;
use PDO;

class PDOAdministradorRepository extends PDOUsuarioRepository implements AdministradorRepository
{
    private Database $db;
    private CacheInterface $cache;

    public function __construct(Database $db, ?CacheInterface $cache = null)
    {
        parent::__construct($db, $cache);
        $this->db = $db;
        $this->cache = $cache ?? CacheFactory::create();
    }

    public function findAllWithFilters(array $filters = []): array
    {
        $cacheKey = "admin:usuarios:filters:" . md5(serialize($filters));

        return $this->cache->remember($cacheKey, function () use ($filters) {
            $conn = $this->db->getConnection();
            
            $tipo = $filters['tipo'] ?? null;
            $estado = $filters['estado'] ?? null;
            $ci = isset($filters['ci']) ? CifradoHelper::encriptar($filters['ci']) : null;
            $limit = (int)($filters['limit'] ?? 100);
            $offset = (int)($filters['offset'] ?? 0);

            $stmt = $conn->prepare("CALL sp_listar_usuarios(?, ?, ?, ?, ?)");
            $stmt->execute([$tipo, $estado, $ci, $limit, $offset]);
            
            $rows = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if (!empty($row['email'])) {
                    $row['email'] = CifradoHelper::desencriptar($row['email']);
                }
                if (!empty($row['ci'])) {
                    $row['ci'] = CifradoHelper::desencriptar($row['ci']);
                }
                $rows[] = $row;
            }
            $stmt->closeCursor();
            $this->db->clearPendingResults();
            
            return $rows;
        }, 1800);
    }

    public function getEstadisticas(): array
    {
        return parent::getEstadisticas();
    }

    public function updateEstado(Uuid $usuarioId, string $nuevoEstado): bool
    {
        return parent::updateEstado($usuarioId, $nuevoEstado);
    }

    public function buscarPorNombre(string $termino, int $limit = 10): array
    {
        // Búsqueda dinámica - sin caché
        $conn = $this->db->getConnection();
        $like = "%{$termino}%";
        $sql = "SELECT u.*, t.Especialidad, t.Cantidad_Actividades 
                FROM usuario u 
                LEFT JOIN Tecnico t ON u.ID_Usuario = t.ID_Tecnico 
                WHERE u.nombre LIKE ? OR u.apellido LIKE ?
                ORDER BY u.nombre ASC LIMIT ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$like, $like, $limit]);
        
        $rows = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if (!empty($row['email'])) {
                $row['email'] = CifradoHelper::desencriptar($row['email']);
            }
            if (!empty($row['ci'])) {
                $row['ci'] = CifradoHelper::desencriptar($row['ci']);
            }
            $rows[] = $row;
        }
        $stmt->closeCursor();
        $this->db->clearPendingResults();
        
        return $rows;
    }

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