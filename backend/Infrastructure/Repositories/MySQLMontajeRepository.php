<?php
/**
 * Infrastructure/Repositories/MySQLMontajeRepository.php
 * Migrado a PDO. TTL: 300s.
 */
namespace maquinas_recreativas\Infrastructure\Repositories;

use PDO;
use maquinas_recreativas\Domain\Montaje\Montaje;
use maquinas_recreativas\Domain\Montaje\MontajeRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Infrastructure\Database\Database;
use maquinas_recreativas\Infrastructure\Cache\CacheInterface;
use maquinas_recreativas\Infrastructure\Cache\CacheFactory;

class MySQLMontajeRepository implements MontajeRepository
{
    private Database       $db;
    private CacheInterface $cache;
    private int            $ttl = 300;

    public function __construct(Database $db, ?CacheInterface $cache = null)
    {
        $this->db    = $db;
        $this->cache = $cache ?? CacheFactory::create();
    }

    public function save(Montaje $montaje): void
    {
        $conn = $this->db->getConnection();
        $data = $montaje->toArray();
        $stmt = $conn->prepare("INSERT INTO montaje (ID_Montaje,ID_Maquina,ID_Componente,ID_Tecnico,detalle,fecha) VALUES (?,?,?,?,?,?)");
        $stmt->execute([
            $data['ID_Montaje'],$data['ID_Maquina'],$data['ID_Componente'],
            $data['ID_Tecnico'],$data['detalle'],$data['fecha']
        ]);

        $this->cache->delete("montajes:maquina:{$data['ID_Maquina']}");
        $this->cache->delete("montajes:componente:{$data['ID_Componente']}");
        $this->cache->delete("montajes:tecnico:{$data['ID_Tecnico']}");
    }

    public function findByMaquina(Uuid $idMaquina): array
    {
        $cacheKey = "montajes:maquina:{$idMaquina->value()}";
        return $this->cache->remember($cacheKey, function () use ($idMaquina) {
            $conn = $this->db->getConnection();
            $stmt = $conn->prepare("SELECT * FROM montaje WHERE ID_Maquina=? ORDER BY fecha DESC");
            $stmt->execute([$idMaquina->value()]);
            $montajes = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $montajes[] = Montaje::fromArray($row);
            }
            return $montajes;
        }, $this->ttl);
    }

    public function findByComponente(Uuid $idComponente): array
    {
        $cacheKey = "montajes:componente:{$idComponente->value()}";
        return $this->cache->remember($cacheKey, function () use ($idComponente) {
            $conn = $this->db->getConnection();
            $stmt = $conn->prepare("SELECT * FROM montaje WHERE ID_Componente=? ORDER BY fecha DESC");
            $stmt->execute([$idComponente->value()]);
            $montajes = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $montajes[] = Montaje::fromArray($row);
            }
            return $montajes;
        }, $this->ttl);
    }

    public function findByTecnico(Uuid $idTecnico): array
    {
        $cacheKey = "montajes:tecnico:{$idTecnico->value()}";
        return $this->cache->remember($cacheKey, function () use ($idTecnico) {
            $conn = $this->db->getConnection();
            $stmt = $conn->prepare("SELECT * FROM montaje WHERE ID_Tecnico=? ORDER BY fecha DESC");
            $stmt->execute([$idTecnico->value()]);
            $montajes = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $montajes[] = Montaje::fromArray($row);
            }
            return $montajes;
        }, $this->ttl);
    }
}