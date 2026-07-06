<?php
/**
 * Infrastructure/Repositories/PDODistribucionRepository.php
 */
namespace maquinas_recreativas\Infrastructure\Repositories;

use maquinas_recreativas\Domain\Distribucion\InformeDistribucion;
use maquinas_recreativas\Domain\Distribucion\DistribucionRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Infrastructure\Database\Database;
use maquinas_recreativas\Infrastructure\Cache\CacheInterface;
use maquinas_recreativas\Infrastructure\Cache\CacheFactory;
use PDO;

class PDODistribucionRepository implements DistribucionRepository
{
    private Database $db;
    private CacheInterface $cache;

    public function __construct(Database $db, ?CacheInterface $cache = null)
    {
        $this->db = $db;
        $this->cache = $cache ?? CacheFactory::create();
    }

    public function save(InformeDistribucion $informe): void
    {
        $conn = $this->db->getConnection();
        $data = $informe->toArray();

        $stmt = $conn->prepare("CALL sp_guardar_informe_distribucion(?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['ID_Distribucion'],
            $data['ID_Maquina'],
            $data['ID_Usuario_Comprobador'],
            $data['ID_Comercio'],
            $data['fecha_alta'],
            $data['estado']
        ]);
        $stmt->closeCursor();
        $this->db->clearPendingResults();

        $this->cache->delete("distribucion:id:{$data['ID_Distribucion']}");
        $this->cache->delete("distribucion:maquina:{$data['ID_Maquina']}");
        $this->invalidateListados();
    }

    public function findById(Uuid $id): ?InformeDistribucion
    {
        $cacheKey = "distribucion:id:{$id->value()}";
        return $this->cache->remember($cacheKey, function () use ($id) {
            $conn = $this->db->getConnection();
            $stmt = $conn->prepare("SELECT * FROM informe_distribucion WHERE ID_Distribucion = ?");
            $stmt->execute([$id->value()]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            $this->db->clearPendingResults();
            
            return $data ? InformeDistribucion::fromArray($data) : null;
        }, 1800);
    }

    public function findByMaquina(Uuid $idMaquina): ?InformeDistribucion
    {
        $cacheKey = "distribucion:maquina:{$idMaquina->value()}";
        return $this->cache->remember($cacheKey, function () use ($idMaquina) {
            $conn = $this->db->getConnection();
            $stmt = $conn->prepare("CALL sp_buscar_distribucion_por_maquina(?)");
            $stmt->execute([$idMaquina->value()]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            $this->db->clearPendingResults();
            
            return $data ? InformeDistribucion::fromArray($data) : null;
        }, 1800);
    }

    public function findAll(array $filters = [], int $limit = 100, int $offset = 0): array
    {
        $cacheKey = "distribuciones:all:" . md5(serialize([$filters, $limit, $offset]));
        return $this->cache->remember($cacheKey, function () use ($filters, $limit, $offset) {
            $conn = $this->db->getConnection();
            
            $estado = $filters['estado'] ?? null;
            $idComercio = $filters['ID_Comercio'] ?? null;
            $idMaquina = $filters['ID_Maquina'] ?? null;
            $fechaInicio = $filters['fecha_inicio'] ?? null;
            $fechaFin = $filters['fecha_fin'] ?? null;

            $stmt = $conn->prepare("CALL sp_listar_distribuciones(?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$estado, $idComercio, $idMaquina, $fechaInicio, $fechaFin, $limit, $offset]);
            
            $informes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            $this->db->clearPendingResults();
            
            return $informes;
        }, 600);
    }

    public function updateEstado(Uuid $idMaquina, string $estado): bool
    {
        $permitidos = ['Operativa', 'Retirada', 'No operativa', 'Distribuyendose'];
        if (!in_array($estado, $permitidos, true)) {
            return false;
        }

        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("CALL sp_actualizar_estado_distribucion(?, ?)");
        $result = $stmt->execute([$idMaquina->value(), $estado]);
        $stmt->closeCursor();
        $this->db->clearPendingResults();

        $this->cache->delete("distribucion:maquina:{$idMaquina->value()}");
        $this->invalidateListados();

        return $result;
    }

    private function invalidateListados(): void
    {
        if ($this->cache instanceof \maquinas_recreativas\Infrastructure\Cache\RedisCache) {
            $this->cache->deleteByPattern("distribuciones:all:*");
        }
    }
}