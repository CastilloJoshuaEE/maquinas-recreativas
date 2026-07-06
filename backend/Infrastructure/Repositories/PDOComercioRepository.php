<?php
/**
 * Infrastructure/Repositories/PDOComercioRepository.php
 */
namespace maquinas_recreativas\Infrastructure\Repositories;

use maquinas_recreativas\Domain\Comercio\Comercio;
use maquinas_recreativas\Domain\Comercio\ComercioRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Infrastructure\Database\Database;
use maquinas_recreativas\Infrastructure\Security\CifradoHelper;
use maquinas_recreativas\Infrastructure\Cache\CacheInterface;
use maquinas_recreativas\Infrastructure\Cache\CacheFactory;
use PDO;

class PDOComercioRepository implements ComercioRepository
{
    private Database $db;
    private CacheInterface $cache;
    private int $ttl = 1800;

    public function __construct(Database $db, ?CacheInterface $cache = null)
    {
        $this->db = $db;
        $this->cache = $cache ?? CacheFactory::create();
    }

    public function guardar(Comercio $comercio): void
    {
        $conn = $this->db->getConnection();
        $data = $comercio->toArray();
        $idValue = $data['id'];

        $existing = $this->buscarPorId($idValue);

        if ($existing) {
            $stmt = $conn->prepare("CALL sp_actualizar_comercio(?, ?, ?, ?, ?)");
            $stmt->execute([
                $idValue,
                $data['nombre'],
                $data['tipo'],
                $data['direccion'],
                $data['telefono']
            ]);
        } else {
            $stmt = $conn->prepare("CALL sp_insertar_comercio(?, ?, ?, ?, ?, ?)");
            $fechaRegistro = $data['fecha_registro'] ?? date('Y-m-d');
            $stmt->execute([
                $idValue,
                $data['nombre'],
                $data['tipo'],
                $data['direccion'],
                $data['telefono'],
                $fechaRegistro
            ]);
        }
        $stmt->closeCursor();
        $this->db->clearPendingResults();

        $this->cache->delete("comercio:id:{$idValue}");
        $this->cache->delete("comercio:nombre:" . md5($data['nombre']));
        $this->invalidateListados();
    }

public function buscarPorId(string $id): ?Comercio
{
    $cacheKey = "comercio:id:{$id}";
    return $this->cache->remember($cacheKey, function () use ($id) {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("CALL sp_buscar_comercio_por_id(?)");
        $stmt->execute([$id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        $this->db->clearPendingResults();

        if (!$data) {
            return null;
        }

        // La función fromArray ahora espera las claves mayúsculas de la DB de manera explícita.
        return Comercio::fromArray($data);
    }, $this->ttl);
}

    public function buscarPorNombre(string $nombre): ?Comercio
    {
        $cacheKey = "comercio:nombre:" . md5($nombre);
        return $this->cache->remember($cacheKey, function () use ($nombre) {
            $conn = $this->db->getConnection();
            $stmt = $conn->prepare("CALL sp_buscar_comercio_por_nombre(?)");
            $stmt->execute([$nombre]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            $this->db->clearPendingResults();

            return $data ? $this->buscarPorId($data['ID_Comercio']) : null;
        }, $this->ttl);
    }

    public function obtenerTodos(array $criterios = []): array
    {
        $cacheKey = "comercios:all:" . md5(serialize($criterios));
        
        return $this->cache->remember($cacheKey, function () use ($criterios) {
            $conn = $this->db->getConnection();
            $tipo = $criterios['tipo'] ?? null;
            $nombre = $criterios['nombre'] ?? null;
            $limit = 1000;
            $offset = 0;

            $stmt = $conn->prepare("CALL sp_listar_comercios(?, ?, ?, ?)");
            $stmt->execute([$tipo, $nombre, $limit, $offset]);
            
            $comercios = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $comercios[] = Comercio::fromArray([
                    'id' => $row['ID_Comercio'],
                    'nombre' => $row['Nombre'] ?? '',
                    'tipo' => $row['Tipo'] ?? '',
                    'direccion' => $row['Direccion'] ?? '',
                    'telefono' => $row['Telefono'] ?? '',
                    'cantidad_maquinas' => $row['Cantidad_Maquinas'] ?? 0,
                    'fecha_registro' => $row['Fecha_Registro'] ?? date('Y-m-d'),
                ]);
            }
            $stmt->closeCursor();
            $this->db->clearPendingResults();
            
            return $comercios;
        }, $this->ttl);
    }

    public function findAll(array $filtros = [], int $offset = 0, int $limit = 10, string $orderBy = 'nombre', string $direction = 'ASC'): array
    {
        $cacheKey = "comercios:list:" . md5(serialize([$filtros, $offset, $limit, $orderBy, $direction]));
        
        return $this->cache->remember($cacheKey, function () use ($filtros, $offset, $limit) {
            $conn = $this->db->getConnection();
            $tipo = $filtros['tipo'] ?? null;
            $nombre = $filtros['nombre'] ?? null;

            $stmt = $conn->prepare("CALL sp_listar_comercios(?, ?, ?, ?)");
            $stmt->execute([$tipo, $nombre, $limit, $offset]);
            
            $comercios = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $comercios[] = Comercio::fromArray([
                    'id' => $row['ID_Comercio'],
                    'nombre' => $row['Nombre'] ?? '',
                    'tipo' => $row['Tipo'] ?? '',
                    'direccion' => $row['Direccion'] ?? '',
                    'telefono' => $row['Telefono'] ?? '',
                    'cantidad_maquinas' => $row['Cantidad_Maquinas'] ?? 0,
                    'fecha_registro' => $row['Fecha_Registro'] ?? date('Y-m-d'),
                ]);
            }
            $stmt->closeCursor();
            $this->db->clearPendingResults();
            
            return $comercios;
        }, $this->ttl);
    }

    public function eliminar(string $id): void
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("CALL sp_eliminar_comercio(?)");
        $stmt->execute([$id]);
        $stmt->closeCursor();
        $this->db->clearPendingResults();

        $this->cache->delete("comercio:id:{$id}");
        $this->invalidateListados();
    }

    public function existePorNombre(string $nombre, ?string $excluirId = null): bool
    {
        $conn = $this->db->getConnection();
        $sql = "SELECT COUNT(*) as total FROM Comercio WHERE Nombre = ?";
        $params = [$nombre];
        if ($excluirId) {
            $sql .= " AND ID_Comercio != ?";
            $params[] = $excluirId;
        }
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        $this->db->clearPendingResults();
        
        return $row['total'] > 0;
    }

    public function tieneMaquinas(string $id): bool
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("CALL sp_comercio_tiene_maquinas(?, @tiene)");
        $stmt->execute([$id]);
        $stmt->closeCursor();
        
        $result = $conn->query("SELECT @tiene as tiene");
        $row = $result->fetch(PDO::FETCH_ASSOC);
        $result->closeCursor();
        $this->db->clearPendingResults();
        
        return (bool)($row['tiene'] ?? 0);
    }

    public function contar(array $criterios = []): int
    {
        $conn = $this->db->getConnection();
        $tipo = $criterios['tipo'] ?? null;
        $nombre = $criterios['nombre'] ?? null;
        
        $stmt = $conn->prepare("CALL sp_contar_comercios(?, ?, @total)");
        $stmt->execute([$tipo, $nombre]);
        $stmt->closeCursor();
        
        $result = $conn->query("SELECT @total as total");
        $row = $result->fetch(PDO::FETCH_ASSOC);
        $result->closeCursor();
        $this->db->clearPendingResults();
        
        return (int)($row['total'] ?? 0);
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