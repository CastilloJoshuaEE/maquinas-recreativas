<?php
/**
 * Infrastructure/Repositories/MySQLTecnicoRepository.php
 */
declare(strict_types=1);

namespace maquinas_recreativas\Infrastructure\Repositories;

use maquinas_recreativas\Domain\Usuario\Tecnico;
use maquinas_recreativas\Domain\Usuario\TecnicoRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Infrastructure\Database\Database;
use maquinas_recreativas\Infrastructure\Cache\CacheInterface;
use maquinas_recreativas\Infrastructure\Cache\CacheFactory;
use PDO;

final class MySQLTecnicoRepository implements TecnicoRepository
{
    private Database $db;
    private MySQLUsuarioRepository $usuarioRepository;
    private CacheInterface $cache;
    private int $ttl = 1800;

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
            $stmt = $conn->prepare("CALL sp_tecnicos_por_especialidad(?)");
            $stmt->execute([$especialidad]);
            
            $tecnicos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            $this->db->clearPendingResults();
            
            return $tecnicos;
        }, $this->ttl);
    }

    public function incrementarActividades(Uuid $tecnicoId): bool
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("CALL sp_incrementar_actividades_tecnico(?)");
        $v = $tecnicoId->value();
        $result = $stmt->execute([$v]);
        $stmt->closeCursor();
        $this->db->clearPendingResults();

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
        $stmt = $conn->prepare("CALL sp_tecnicos_disponibles_por_especialidad(?)");
        $stmt->execute([$especialidad]);

        $tecnicos = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // NO desencriptar aquí: hydrate() ya lo hace internamente
            $tecnicos[] = $this->usuarioRepository->hydrate($row);
        }
        $stmt->closeCursor();
        $this->db->clearPendingResults();

        return $tecnicos;
    }, $this->ttl);
}
}