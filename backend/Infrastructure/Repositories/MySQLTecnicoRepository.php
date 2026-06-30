<?php
/**
 * Infrastructure/Repositories/MySQLTecnicoRepository.php
 * Migrado a PDO. TTL: 1800 s — datos estables.
 */
declare(strict_types=1);

namespace maquinas_recreativas\Infrastructure\Repositories;

use PDO;
use maquinas_recreativas\Domain\Usuario\Tecnico;
use maquinas_recreativas\Domain\Usuario\TecnicoRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Infrastructure\Database\Database;
use maquinas_recreativas\Infrastructure\Cache\CacheInterface;
use maquinas_recreativas\Infrastructure\Cache\CacheFactory;

final class MySQLTecnicoRepository implements TecnicoRepository
{
    private Database              $db;
    private MySQLUsuarioRepository $usuarioRepository;
    private CacheInterface         $cache;
    private int                    $ttl = 1800;

    public function __construct(Database $db, ?CacheInterface $cache = null)
    {
        $this->db = $db;
        $this->cache = $cache ?? CacheFactory::create();
        $this->usuarioRepository = new MySQLUsuarioRepository($db, $this->cache);
    }

    public function findByEspecialidad(string $especialidad): array
    {
        $cacheKey = "tecnicos:especialidad:{$especialidad}";

        return $this->cache->remember($cacheKey, function () use ($especialidad) {
            $conn = $this->db->getConnection();
            $sql = "SELECT u.*, t.Especialidad, t.Cantidad_Actividades 
                    FROM usuario u
                    INNER JOIN Tecnico t ON u.ID_Usuario = t.ID_Tecnico
                    WHERE t.Especialidad = ?
                    ORDER BY u.nombre ASC";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([$especialidad]);
            
            $tecnicos = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $tecnicos[] = $this->usuarioRepository->hydrate($row);
            }
            return $tecnicos;
        }, $this->ttl);
    }

    public function incrementarActividades(Uuid $tecnicoId): bool
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare(
            "UPDATE Tecnico SET Cantidad_Actividades = Cantidad_Actividades + 1 WHERE ID_Tecnico = ?"
        );
        $v = $tecnicoId->value();
        $result = $stmt->execute([$v]);

        $this->cache->delete("usuario:id:{$v}");
        if ($this->cache instanceof \maquinas_recreativas\Infrastructure\Cache\RedisCache) {
            $this->cache->deleteByPattern("tecnicos:especialidad:*");
            $this->cache->deleteByPattern("tecnicos:disponibles:*");
        }

        return $result;
    }

    public function findAvailableByEspecialidad(string $especialidad): array
    {
        $cacheKey = "tecnicos:disponibles:{$especialidad}";

        return $this->cache->remember($cacheKey, function () use ($especialidad) {
            $conn = $this->db->getConnection();
            $sql = "SELECT u.*, t.Especialidad, t.Cantidad_Actividades 
                    FROM usuario u
                    INNER JOIN Tecnico t ON u.ID_Usuario = t.ID_Tecnico
                    WHERE t.Especialidad = ? 
                      AND u.estado = 'Activo'
                    ORDER BY t.Cantidad_Actividades ASC";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([$especialidad]);
            
            $tecnicos = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $tecnicos[] = $this->usuarioRepository->hydrate($row);
            }
            return $tecnicos;
        }, $this->ttl);
    }
}