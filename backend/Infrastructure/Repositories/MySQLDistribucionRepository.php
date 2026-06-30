<?php
/**
 * Infrastructure/Repositories/MySQLDistribucionRepository.php
 * Migrado a PDO. UPSERT dual (MySQL ON DUPLICATE KEY / Postgres ON CONFLICT).
 */
namespace maquinas_recreativas\Infrastructure\Repositories;

use PDO;
use maquinas_recreativas\Domain\Distribucion\InformeDistribucion;
use maquinas_recreativas\Domain\Distribucion\DistribucionRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Infrastructure\Database\Database;
use maquinas_recreativas\Infrastructure\Cache\CacheInterface;
use maquinas_recreativas\Infrastructure\Cache\CacheFactory;

class MySQLDistribucionRepository implements DistribucionRepository
{
    private Database       $db;
    private CacheInterface $cache;

    public function __construct(Database $db, ?CacheInterface $cache = null)
    {
        $this->db    = $db;
        $this->cache = $cache ?? CacheFactory::create();
    }

    public function save(InformeDistribucion $informe): void
    {
        $conn = $this->db->getConnection();
        $data = $informe->toArray();

        if ($this->db->isPostgres()) {
            $sql = "INSERT INTO informe_distribucion (ID_Distribucion,ID_Maquina,ID_Usuario_Comprobador,ID_Comercio,fecha_alta,fecha_baja,estado)
                    VALUES (?,?,?,?,?,?,?)
                    ON CONFLICT (ID_Distribucion) DO UPDATE SET estado=EXCLUDED.estado, fecha_baja=EXCLUDED.fecha_baja";
        } else {
            $sql = "INSERT INTO informe_distribucion (ID_Distribucion,ID_Maquina,ID_Usuario_Comprobador,ID_Comercio,fecha_alta,fecha_baja,estado)
                    VALUES (?,?,?,?,?,?,?)
                    ON DUPLICATE KEY UPDATE estado=VALUES(estado), fecha_baja=VALUES(fecha_baja)";
        }
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            $data['ID_Distribucion'], $data['ID_Maquina'], $data['ID_Usuario_Comprobador'],
            $data['ID_Comercio'], $data['fecha_alta'], $data['fecha_baja'], $data['estado']
        ]);

        $this->cache->delete("distribucion:id:{$data['ID_Distribucion']}");
        $this->cache->delete("distribucion:maquina:{$data['ID_Maquina']}");
        $this->invalidateListados();
    }

    public function findById(Uuid $id): ?InformeDistribucion
    {
        $cacheKey = "distribucion:id:{$id->value()}";
        return $this->cache->remember($cacheKey, function () use ($id) {
            $conn = $this->db->getConnection();
            $stmt = $conn->prepare("SELECT * FROM informe_distribucion WHERE ID_Distribucion=?");
            $stmt->execute([$id->value()]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $data ? InformeDistribucion::fromArray($data) : null;
        }, 1800);
    }

    public function findByMaquina(Uuid $idMaquina): ?InformeDistribucion
    {
        $cacheKey = "distribucion:maquina:{$idMaquina->value()}";
        return $this->cache->remember($cacheKey, function () use ($idMaquina) {
            $conn = $this->db->getConnection();
            $stmt = $conn->prepare("SELECT * FROM informe_distribucion WHERE ID_Maquina=?");
            $stmt->execute([$idMaquina->value()]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $data ? InformeDistribucion::fromArray($data) : null;
        }, 1800);
    }

    public function findAll(array $filters = [], int $limit = 100, int $offset = 0): array
    {
        $cacheKey = "distribuciones:all:" . md5(serialize([$filters,$limit,$offset]));
        return $this->cache->remember($cacheKey, function () use ($filters, $limit, $offset) {
            $conn = $this->db->getConnection();

            $sql = "SELECT id.*, m.Nombre_Maquina,
                           CONCAT(u.nombre, ' ', u.apellido) as Nombre_Tecnico,
                           c.Nombre as Nombre_Comercio, c.Direccion as Direccion_Comercio,
                           c.Telefono as Telefono_Comercio, c.Tipo as Tipo_Comercio
                    FROM informe_distribucion id
                    INNER JOIN MaquinaRecreativa m ON id.ID_Maquina = m.ID_Maquina
                    INNER JOIN usuario u ON id.ID_Usuario_Comprobador = u.ID_Usuario
                    INNER JOIN Comercio c ON id.ID_Comercio = c.ID_Comercio
                    WHERE id.fecha_alta = (
                        SELECT MAX(id2.fecha_alta)
                        FROM informe_distribucion id2
                        WHERE id2.ID_Maquina = id.ID_Maquina
                    )";

            $params = [];

            if (!empty($filters['estado'])) { $sql .= " AND id.estado = ?"; $params[] = $filters['estado']; }
            if (!empty($filters['ID_Comercio'])) { $sql .= " AND id.ID_Comercio = ?"; $params[] = $filters['ID_Comercio']; }
            if (!empty($filters['ID_Maquina'])) { $sql .= " AND id.ID_Maquina = ?"; $params[] = $filters['ID_Maquina']; }
            if (!empty($filters['fecha_inicio'])) { $sql .= " AND DATE(id.fecha_alta) >= ?"; $params[] = $filters['fecha_inicio']; }
            if (!empty($filters['fecha_fin'])) { $sql .= " AND DATE(id.fecha_alta) <= ?"; $params[] = $filters['fecha_fin']; }

            $sql .= " ORDER BY id.fecha_alta DESC LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;

            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            $informes = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $informes[] = $row;
            }

            error_log("Informes encontrados (únicos por máquina): " . count($informes));
            return $informes;
        }, 600);
    }

    public function updateEstado(Uuid $idMaquina, string $estado): bool
    {
        $permitidos = ['Operativa','Retirada','No operativa','Distribuyendose'];
        if (!in_array($estado, $permitidos, true)) return false;
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("UPDATE informe_distribucion SET estado=? WHERE ID_Maquina=?");
        $v    = $idMaquina->value();
        $result = $stmt->execute([$estado, $v]);
        $this->cache->delete("distribucion:maquina:{$v}");
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