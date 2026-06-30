<?php
/**
 * Infrastructure/Repositories/MySQLComercioRepository.php
 * Migrado a PDO. TTL: 1800s.
 */
namespace maquinas_recreativas\Infrastructure\Repositories;

use PDO;
use maquinas_recreativas\Domain\Comercio\Comercio;
use maquinas_recreativas\Domain\Comercio\ComercioRepository;
use maquinas_recreativas\Infrastructure\Database\Database;
use maquinas_recreativas\Infrastructure\Cache\CacheInterface;
use maquinas_recreativas\Infrastructure\Cache\CacheFactory;

class MySQLComercioRepository implements ComercioRepository
{
    private Database       $db;
    private CacheInterface $cache;
    private int            $ttl = 1800;

    public function __construct(Database $db, ?CacheInterface $cache = null)
    {
        $this->db    = $db;
        $this->cache = $cache ?? CacheFactory::create();
    }

    public function guardar(Comercio $comercio): void
    {
        $conn = $this->db->getConnection();
        $data = $comercio->toArray();

        $checkStmt = $conn->prepare("SELECT COUNT(*) as total FROM Comercio WHERE ID_Comercio=?");
        $idValue   = $data['id'];
        $checkStmt->execute([$idValue]);
        $exists = $checkStmt->fetch(PDO::FETCH_ASSOC)['total'] > 0;

        if ($exists) {
            $sql  = "UPDATE Comercio SET Nombre=?,Tipo=?,Direccion=?,Telefono=? WHERE ID_Comercio=?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$data['nombre'], $data['tipo'], $data['direccion'], $data['telefono'], $idValue]);
        } else {
            $sql  = "INSERT INTO Comercio (ID_Comercio,Nombre,Tipo,Direccion,Telefono,Fecha_Registro) VALUES (?,?,?,?,?,?)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$idValue, $data['nombre'], $data['tipo'], $data['direccion'], $data['telefono'], $data['fecha_registro']]);
        }

        $this->cache->delete("comercio:id:{$idValue}");
        $this->cache->delete("comercio:nombre:" . md5($data['nombre']));
        $this->invalidateListados();
    }

    public function buscarPorId(string $id): ?Comercio
    {
        $cacheKey = "comercio:id:{$id}";
        return $this->cache->remember($cacheKey, function () use ($id) {
            $conn = $this->db->getConnection();
            $stmt = $conn->prepare("SELECT * FROM Comercio WHERE ID_Comercio=?");
            $stmt->execute([$id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$data) return null;

            $data['nombre']            = $data['Nombre']            ?? '';
            $data['tipo']              = $data['Tipo']              ?? '';
            $data['direccion']         = $data['Direccion']         ?? '';
            $data['telefono']          = $data['Telefono']          ?? '';
            $data['cantidad_maquinas'] = $data['Cantidad_Maquinas'] ?? 0;
            $data['fecha_registro']    = $data['Fecha_Registro']    ?? date('Y-m-d');
            return Comercio::fromArray($data);
        }, $this->ttl);
    }

    public function buscarPorNombre(string $nombre): ?Comercio
    {
        $cacheKey = "comercio:nombre:" . md5($nombre);
        return $this->cache->remember($cacheKey, function () use ($nombre) {
            $conn = $this->db->getConnection();
            $stmt = $conn->prepare("SELECT ID_Comercio FROM Comercio WHERE Nombre=?");
            $stmt->execute([$nombre]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ? $this->buscarPorId($row['ID_Comercio']) : null;
        }, $this->ttl);
    }

    public function obtenerTodos(array $criterios = []): array
    {
        $cacheKey = "comercios:all:" . md5(serialize($criterios));

        return $this->cache->remember($cacheKey, function () use ($criterios) {
            $conn = $this->db->getConnection();
            $sql = "SELECT ID_Comercio, Nombre, Tipo, Direccion, Telefono, Fecha_Registro
                    FROM Comercio
                    WHERE 1=1";
            $params = [];

            if (!empty($criterios['tipo'])) {
                $sql .= " AND Tipo = ?";
                $params[] = $criterios['tipo'];
            }
            if (!empty($criterios['nombre'])) {
                $sql .= " AND Nombre LIKE ?";
                $params[] = "%{$criterios['nombre']}%";
            }

            $sql .= " ORDER BY Nombre ASC";

            $stmt = $conn->prepare($sql);
            $stmt->execute($params);

            $comercios = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $row['cantidad_maquinas'] = 0;
                $comercios[] = Comercio::fromArray($row);
            }
            return $comercios;
        }, $this->ttl);
    }

    public function findAll(array $filtros = [], int $offset = 0, int $limit = 10, string $orderBy = 'nombre', string $direction = 'ASC'): array
    {
        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
        $orderBy   = in_array($orderBy, ['nombre','tipo','fecha_registro']) ? $orderBy : 'nombre';
        $cacheKey  = "comercios:list:" . md5(serialize([$filtros,$offset,$limit,$orderBy,$direction]));

        return $this->cache->remember($cacheKey, function () use ($filtros, $offset, $limit, $orderBy, $direction) {
            $conn = $this->db->getConnection();
            $sql = "SELECT ID_Comercio, Nombre, Tipo, Direccion, Telefono, Fecha_Registro
                    FROM Comercio
                    WHERE 1=1";
            $params = [];

            if (!empty($filtros['tipo'])) {
                $sql .= " AND Tipo=?";
                $params[] = $filtros['tipo'];
            }
            if (!empty($filtros['nombre'])) {
                $sql .= " AND Nombre LIKE ?";
                $params[] = "%{$filtros['nombre']}%";
            }

            $sql .= " ORDER BY {$orderBy} {$direction} LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;

            $stmt = $conn->prepare($sql);
            $stmt->execute($params);

            $comercios = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $row['cantidad_maquinas'] = 0;
                $comercios[] = Comercio::fromArray($row);
            }
            return $comercios;
        }, $this->ttl);
    }

    public function eliminar(string $id): void
    {
        $conn = $this->db->getConnection();
        try {
            $conn->beginTransaction();
            if ($this->tieneMaquinas($id)) {
                throw new \RuntimeException('No se puede eliminar el comercio porque tiene máquinas asociadas');
            }
            $stmt = $conn->prepare("DELETE FROM Comercio WHERE ID_Comercio=?");
            $stmt->execute([$id]);
            $conn->commit();
        } catch (\Exception $e) {
            if ($conn->inTransaction()) $conn->rollBack();
            throw new \RuntimeException("Error al eliminar comercio: " . $e->getMessage(), 0, $e);
        }
        $this->cache->delete("comercio:id:{$id}");
        $this->invalidateListados();
    }

    public function existePorNombre(string $nombre, ?string $excluirId = null): bool
    {
        $conn = $this->db->getConnection();
        $sql = "SELECT COUNT(*) as total FROM Comercio WHERE Nombre=?";
        $params = [$nombre];
        if ($excluirId) {
            $sql .= " AND ID_Comercio!=?";
            $params[] = $excluirId;
        }
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'] > 0;
    }

    public function tieneMaquinas(string $id): bool
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM MaquinaRecreativa WHERE ID_Comercio=?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'] > 0;
    }

    public function contar(array $criterios = []): int
    {
        $conn = $this->db->getConnection();
        $sql = "SELECT COUNT(*) as total FROM Comercio WHERE 1=1";
        $params = [];

        if (!empty($criterios['tipo'])) {
            $sql .= " AND Tipo=?";
            $params[] = $criterios['tipo'];
        }
        if (!empty($criterios['nombre'])) {
            $sql .= " AND Nombre LIKE ?";
            $params[] = "%{$criterios['nombre']}%";
        }

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$row['total'];
    }

    public function count(array $filtros = []): int
    {
        return $this->contar($filtros);
    }

    private function invalidateListados(): void
    {
        if ($this->cache instanceof \maquinas_recreativas\Infrastructure\Cache\RedisCache) {
            $this->cache->deleteByPattern("comercios:all:*");
            $this->cache->deleteByPattern("comercios:list:*");
        }
    }
}