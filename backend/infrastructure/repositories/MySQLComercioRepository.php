<?php
/**
 * Infrastructure/Repositories/MySQLComercioRepository.php
 * TTL: 1800 s (datos moderadamente estables)
 */
namespace maquinas_recreativas\Infrastructure\Repositories;

use maquinas_recreativas\Domain\Comercio\Comercio;
use maquinas_recreativas\Domain\Comercio\ComercioRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Infrastructure\Database\Database;
use maquinas_recreativas\Infrastructure\Security\CifradoHelper;
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
        $checkStmt->bind_param('s', $idValue);
        $checkStmt->execute();
        $exists = $checkStmt->get_result()->fetch_assoc()['total'] > 0;
        $checkStmt->close();

        if ($exists) {
            $sql  = "UPDATE Comercio SET Nombre=?,Tipo=?,Direccion=?,Telefono=? WHERE ID_Comercio=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('sssss', $data['nombre'], $data['tipo'], $data['direccion'], $data['telefono'], $idValue);
        } else {
            $sql  = "INSERT INTO Comercio (ID_Comercio,Nombre,Tipo,Direccion,Telefono,Fecha_Registro) VALUES (?,?,?,?,?,?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ssssss', $idValue, $data['nombre'], $data['tipo'], $data['direccion'], $data['telefono'], $data['fecha_registro']);
        }
        $stmt->execute(); $stmt->close();

        // Invalidar
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
            $stmt->bind_param('s', $id);
            $stmt->execute();
            $data = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if (!$data) return null;
            $data['nombre']           = $data['Nombre']           ?? '';
            $data['tipo']             = $data['Tipo']             ?? '';
            $data['direccion']        = $data['Direccion']        ?? '';
            $data['telefono']         = $data['Telefono']         ?? '';
            $data['cantidad_maquinas']= $data['Cantidad_Maquinas']?? 0;
            $data['fecha_registro']   = $data['Fecha_Registro']   ?? date('Y-m-d');
            return Comercio::fromArray($data);
        }, $this->ttl);
    }

    public function buscarPorNombre(string $nombre): ?Comercio
    {
        $cacheKey = "comercio:nombre:" . md5($nombre);
        return $this->cache->remember($cacheKey, function () use ($nombre) {
            $conn = $this->db->getConnection();
            $stmt = $conn->prepare("SELECT ID_Comercio FROM Comercio WHERE Nombre=?");
            $stmt->bind_param('s', $nombre);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $row ? $this->buscarPorId($row['ID_Comercio']) : null;
        }, $this->ttl);
    }

    public function obtenerTodos(array $criterios = []): array
    {
        $cacheKey = "comercios:all:" . md5(serialize($criterios));
        return $this->cache->remember($cacheKey, function () use ($criterios) {
            $conn   = $this->db->getConnection();
            $sql    = "SELECT c.*, COUNT(m.ID_Maquina) as cantidad_maquinas 
                       FROM Comercio c LEFT JOIN MaquinaRecreativa m ON c.ID_Comercio=m.ID_Comercio WHERE 1=1";
            $params = []; $types = "";
            if (!empty($criterios['tipo']))   { $sql .= " AND c.Tipo=?";        $params[] = $criterios['tipo'];         $types .= "s"; }
            if (!empty($criterios['nombre'])) { $sql .= " AND c.Nombre LIKE ?"; $params[] = "%{$criterios['nombre']}%"; $types .= "s"; }
            $sql .= " GROUP BY c.ID_Comercio ORDER BY c.Nombre ASC";
            $stmt = $conn->prepare($sql);
            if (!empty($params)) $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $comercios = [];
            while ($row = $stmt->get_result()->fetch_assoc()) $comercios[] = Comercio::fromArray($row);
            $stmt->close();
            return $comercios;
        }, $this->ttl);
    }

    public function findAll(array $filtros = [], int $offset = 0, int $limit = 10, string $orderBy = 'nombre', string $direction = 'ASC'): array
    {
        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
        $orderBy   = in_array($orderBy, ['nombre','tipo','fecha_registro']) ? $orderBy : 'nombre';
        $cacheKey  = "comercios:list:" . md5(serialize([$filtros,$offset,$limit,$orderBy,$direction]));
        return $this->cache->remember($cacheKey, function () use ($filtros,$offset,$limit,$orderBy,$direction) {
            $conn   = $this->db->getConnection();
            $sql    = "SELECT c.*, COUNT(m.ID_Maquina) as cantidad_maquinas 
                       FROM Comercio c LEFT JOIN MaquinaRecreativa m ON c.ID_Comercio=m.ID_Comercio WHERE 1=1";
            $params = []; $types = "";
            if (!empty($filtros['tipo']))   { $sql .= " AND c.Tipo=?";        $params[] = $filtros['tipo'];         $types .= "s"; }
            if (!empty($filtros['nombre'])) { $sql .= " AND c.Nombre LIKE ?"; $params[] = "%{$filtros['nombre']}%"; $types .= "s"; }
            $sql .= " GROUP BY c.ID_Comercio ORDER BY c.{$orderBy} {$direction} LIMIT ? OFFSET ?";
            $params[] = $limit; $params[] = $offset; $types .= "ii";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $comercios = [];
            while ($row = $stmt->get_result()->fetch_assoc()) $comercios[] = Comercio::fromArray($row);
            $stmt->close();
            return $comercios;
        }, $this->ttl);
    }

    public function eliminar(string $id): void
    {
        $conn = $this->db->getConnection();
        try {
            $conn->begin_transaction();
            if ($this->tieneMaquinas($id)) throw new \RuntimeException('No se puede eliminar el comercio porque tiene máquinas asociadas');
            $stmt = $conn->prepare("DELETE FROM Comercio WHERE ID_Comercio=?");
            $stmt->bind_param('s', $id);
            $stmt->execute(); $stmt->close();
            $conn->commit();
        } catch (\Exception $e) {
            $conn->rollback();
            throw new \RuntimeException("Error al eliminar comercio: " . $e->getMessage(), 0, $e);
        }
        $this->cache->delete("comercio:id:{$id}");
        $this->invalidateListados();
    }

    public function existePorNombre(string $nombre, ?string $excluirId = null): bool
    {
        $conn   = $this->db->getConnection();
        $sql    = "SELECT COUNT(*) as total FROM Comercio WHERE Nombre=?";
        $params = [$nombre]; $types = "s";
        if ($excluirId) { $sql .= " AND ID_Comercio!=?"; $params[] = $excluirId; $types .= "s"; }
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row['total'] > 0;
    }

    public function tieneMaquinas(string $id): bool
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM MaquinaRecreativa WHERE ID_Comercio=?");
        $stmt->bind_param('s', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row['total'] > 0;
    }

    public function contar(array $criterios = []): int
    {
        $conn   = $this->db->getConnection();
        $sql    = "SELECT COUNT(*) as total FROM Comercio WHERE 1=1";
        $params = []; $types = "";
        if (!empty($criterios['tipo']))   { $sql .= " AND Tipo=?";        $params[] = $criterios['tipo'];         $types .= "s"; }
        if (!empty($criterios['nombre'])) { $sql .= " AND Nombre LIKE ?"; $params[] = "%{$criterios['nombre']}%"; $types .= "s"; }
        $stmt = $conn->prepare($sql);
        if (!empty($params)) $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return (int)$row['total'];
    }

    public function count(array $filtros = []): int { return $this->contar($filtros); }

    private function invalidateListados(): void
    {
        if ($this->cache instanceof \maquinas_recreativas\Infrastructure\Cache\RedisCache) {
            $this->cache->deleteByPattern("comercios:all:*");
            $this->cache->deleteByPattern("comercios:list:*");
        }
    }
}
