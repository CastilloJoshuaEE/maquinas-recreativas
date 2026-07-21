<?php
/**
 * Infrastructure/Repositories/PDOMontajeRepository.php
 */
namespace maquinas_recreativas\Infrastructure\Repositories;

use maquinas_recreativas\Domain\Montaje\Montaje;
use maquinas_recreativas\Domain\Montaje\MontajeRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Infrastructure\Database\Database;
use maquinas_recreativas\Infrastructure\Cache\CacheInterface;
use maquinas_recreativas\Infrastructure\Cache\CacheFactory;
use PDO;

class PDOMontajeRepository implements MontajeRepository
{
    private Database $db;
    private CacheInterface $cache;
    private int $ttl = 300;

    public function __construct(Database $db, ?CacheInterface $cache = null)
    {
        $this->db = $db;
        $this->cache = $cache ?? CacheFactory::create();
    }

    public function save(Montaje $montaje): void
    {
        $data = $montaje->toArray();

        $stmt = $this->db->prepareCall('CALL sp_insertar_montaje(?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $data['ID_Montaje'],
            $data['ID_Maquina'],
            $data['ID_Componente'],
            $data['ID_Tecnico'],
            $data['detalle'],
            $data['fecha'],
        ]);
        $stmt->closeCursor();

        $this->cache->delete("montajes:maquina:{$data['ID_Maquina']}");
        $this->cache->delete("montajes:componente:{$data['ID_Componente']}");
        $this->cache->delete("montajes:tecnico:{$data['ID_Tecnico']}");
    }

    public function findByMaquina(Uuid $idMaquina): array
    {
        $cacheKey = "montajes:maquina:{$idMaquina->value()}";
        return $this->cache->remember($cacheKey, function () use ($idMaquina) {
            $stmt = $this->db->prepareCall('CALL sp_montajes_por_maquina(?)');
            $stmt->execute([$idMaquina->value()]);

            $montajes = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $montajes[] = Montaje::fromArray($row);
            }
            $stmt->closeCursor();

            return $montajes;
        }, $this->ttl);
    }

    public function findByComponente(Uuid $idComponente): array
    {
        $cacheKey = "montajes:componente:{$idComponente->value()}";
        return $this->cache->remember($cacheKey, function () use ($idComponente) {
            $stmt = $this->db->prepareCall('CALL sp_montajes_por_componente(?)');
            $stmt->execute([$idComponente->value()]);

            $montajes = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $montajes[] = Montaje::fromArray($row);
            }
            $stmt->closeCursor();

            return $montajes;
        }, $this->ttl);
    }

    public function findByTecnico(Uuid $idTecnico): array
    {
        $cacheKey = "montajes:tecnico:{$idTecnico->value()}";
        return $this->cache->remember($cacheKey, function () use ($idTecnico) {
            $stmt = $this->db->prepareCall('CALL sp_montajes_por_tecnico(?)');
            $stmt->execute([$idTecnico->value()]);

            $montajes = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $montajes[] = Montaje::fromArray($row);
            }
            $stmt->closeCursor();

            return $montajes;
        }, $this->ttl);
    }
}