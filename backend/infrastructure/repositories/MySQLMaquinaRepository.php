<?php
/**
 * Infrastructure/Repositories/MySQLMaquinaRepository.php
 *
 * TTL caché: 1800 s (30 min) — datos estables.
 * Claves:  maquina:id:{uuid}
 *          maquinas:ensamblador:{uuid}
 *          maquinas:comprobador:{uuid}
 *          maquinas:mantenimiento:{uuid}
 *          maquinas:estado:{estado}
 *          maquinas:etapa:{etapa}
 *          maquinas:distribucion
 *          maquinas:operativas_comercio:{comercioId}
 * Invalida: save() elimina todas las claves relevantes.
 */

namespace maquinas_recreativas\Infrastructure\Repositories;

use maquinas_recreativas\Domain\Maquina\MaquinaRecreativa;
use maquinas_recreativas\Domain\Maquina\MaquinaRepository;
use maquinas_recreativas\Domain\Maquina\EstadoMaquina;
use maquinas_recreativas\Domain\Maquina\EtapaMaquina;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Domain\Comercio\Comercio;
use maquinas_recreativas\Domain\Componente\Componente;
use maquinas_recreativas\Infrastructure\Database\Database;
use maquinas_recreativas\Infrastructure\Cache\CacheInterface;
use maquinas_recreativas\Infrastructure\Cache\CacheFactory;

class MySQLMaquinaRepository implements MaquinaRepository
{
    private Database       $db;
    private CacheInterface $cache;
    private int            $ttl = 1800;

    public function __construct(Database $db, ?CacheInterface $cache = null)
    {
        $this->db    = $db;
        $this->cache = $cache ?? CacheFactory::create();
    }

    // -------------------------------------------------------------------------
    // Lectura con caché
    // -------------------------------------------------------------------------

    public function findById(Uuid $id): ?MaquinaRecreativa
    {
        $cacheKey = "maquina:id:{$id->value()}";

        return $this->cache->remember($cacheKey, function () use ($id) {
            $conn = $this->db->getConnection();
            $sql  = "SELECT * FROM MaquinaRecreativa WHERE ID_Maquina = ?";
            $stmt = $conn->prepare($sql);
            $v    = $id->value();
            $stmt->bind_param('s', $v);
            $stmt->execute();
            $data = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $data ? MaquinaRecreativa::fromArray($data) : null;
        }, $this->ttl);
    }

    public function findByTecnicoEnsamblador(Uuid $idTecnico): array
    {
        $cacheKey = "maquinas:ensamblador:{$idTecnico->value()}";

        return $this->cache->remember($cacheKey, function () use ($idTecnico) {
            $conn = $this->db->getConnection();
            $sql  = "SELECT * FROM MaquinaRecreativa 
                     WHERE ID_Tecnico_Ensamblador = ? 
                       AND (Estado = 'Ensamblandose' OR Estado = 'Reensamblandose')
                     ORDER BY Fecha_Registro DESC";
            $stmt = $conn->prepare($sql);
            $v    = $idTecnico->value();
            $stmt->bind_param('s', $v);
            $stmt->execute();
            $result = $stmt->get_result();
            $maquinas = [];
            while ($row = $result->fetch_assoc()) {
                $maquinas[] = MaquinaRecreativa::fromArray($row);
            }
            $stmt->close();
            return $maquinas;
        }, $this->ttl);
    }

    public function findByTecnicoComprobador(Uuid $idTecnico): array
    {
        $cacheKey = "maquinas:comprobador:{$idTecnico->value()}";

        return $this->cache->remember($cacheKey, function () use ($idTecnico) {
            $conn = $this->db->getConnection();
            $sql  = "SELECT * FROM MaquinaRecreativa 
                     WHERE ID_Tecnico_Comprobador = ? AND Estado = 'Comprobandose'
                     ORDER BY Fecha_Registro DESC";
            $stmt = $conn->prepare($sql);
            $v    = $idTecnico->value();
            $stmt->bind_param('s', $v);
            $stmt->execute();
            $result   = $stmt->get_result();
            error_log("findByTecnicoComprobador: técnico $v, filas=" . $result->num_rows);
            $maquinas = [];
            while ($row = $result->fetch_assoc()) {
                $maquinas[] = MaquinaRecreativa::fromArray($row);
            }
            $stmt->close();
            return $maquinas;
        }, $this->ttl);
    }

    public function findByTecnicoMantenimiento(Uuid $idTecnico): array
    {
        $cacheKey = "maquinas:mantenimiento:{$idTecnico->value()}";

        return $this->cache->remember($cacheKey, function () use ($idTecnico) {
            $conn = $this->db->getConnection();
            $sql  = "SELECT * FROM MaquinaRecreativa 
                     WHERE ID_Tecnico_Mantenimiento = ? AND Estado = 'No operativa'
                     ORDER BY Fecha_Registro DESC";
            $stmt = $conn->prepare($sql);
            $v    = $idTecnico->value();
            $stmt->bind_param('s', $v);
            $stmt->execute();
            $maquinas = [];
            while ($row = $stmt->get_result()->fetch_assoc()) {
                $maquinas[] = MaquinaRecreativa::fromArray($row);
            }
            $stmt->close();
            return $maquinas;
        }, $this->ttl);
    }

    public function findByEstado(EstadoMaquina $estado): array
    {
        $cacheKey = "maquinas:estado:{$estado->value()}";

        return $this->cache->remember($cacheKey, function () use ($estado) {
            $conn = $this->db->getConnection();
            $sql  = "SELECT * FROM MaquinaRecreativa WHERE Estado = ? ORDER BY Fecha_Registro DESC";
            $stmt = $conn->prepare($sql);
            $v    = $estado->value();
            $stmt->bind_param('s', $v);
            $stmt->execute();
            $maquinas = [];
            while ($row = $stmt->get_result()->fetch_assoc()) {
                $maquinas[] = MaquinaRecreativa::fromArray($row);
            }
            $stmt->close();
            return $maquinas;
        }, $this->ttl);
    }

    public function findByEtapa(EtapaMaquina $etapa): array
    {
        $cacheKey = "maquinas:etapa:{$etapa->value()}";

        return $this->cache->remember($cacheKey, function () use ($etapa) {
            $conn = $this->db->getConnection();
            $sql  = "SELECT * FROM MaquinaRecreativa WHERE Etapa = ? ORDER BY Fecha_Registro DESC";
            $stmt = $conn->prepare($sql);
            $v    = $etapa->value();
            $stmt->bind_param('s', $v);
            $stmt->execute();
            $maquinas = [];
            while ($row = $stmt->get_result()->fetch_assoc()) {
                $maquinas[] = MaquinaRecreativa::fromArray($row);
            }
            $stmt->close();
            return $maquinas;
        }, $this->ttl);
    }

    public function findParaDistribucion(): array
    {
        $cacheKey = "maquinas:distribucion";

        return $this->cache->remember($cacheKey, function () {
            return $this->findByEtapaAndEstado(EtapaMaquina::DISTRIBUCION(), EstadoMaquina::DISTRIBUYENDOSE());
        }, $this->ttl);
    }

    private function findByEtapaAndEstado(EtapaMaquina $etapa, EstadoMaquina $estado): array
    {
        $conn = $this->db->getConnection();
        $sql  = "SELECT * FROM MaquinaRecreativa WHERE Etapa = ? AND Estado = ? ORDER BY Fecha_Registro DESC";
        $stmt = $conn->prepare($sql);
        $ev   = $etapa->value();
        $sv   = $estado->value();
        $stmt->bind_param('ss', $ev, $sv);
        $stmt->execute();
        $maquinas = [];
        while ($row = $stmt->get_result()->fetch_assoc()) {
            $maquinas[] = MaquinaRecreativa::fromArray($row);
        }
        $stmt->close();
        return $maquinas;
    }

    public function findOperativasPorComercio(Comercio $comercio): array
    {
        $cid      = $comercio->getId();
        $cacheKey = "maquinas:operativas_comercio:{$cid}";

        return $this->cache->remember($cacheKey, function () use ($cid) {
            $conn = $this->db->getConnection();
            $sql  = "SELECT * FROM MaquinaRecreativa 
                     WHERE ID_Comercio = ? AND Estado = 'Operativa' AND Etapa = 'Recaudacion'
                     ORDER BY Nombre_Maquina ASC";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('s', $cid);
            $stmt->execute();
            $maquinas = [];
            while ($row = $stmt->get_result()->fetch_assoc()) {
                $maquinas[] = MaquinaRecreativa::fromArray($row);
            }
            $stmt->close();
            return $maquinas;
        }, $this->ttl);
    }

    public function getComponentesMontaje(MaquinaRecreativa $maquina): array
    {
        $mid      = $maquina->id()->value();
        $cacheKey = "maquinas:componentes_montaje:{$mid}";

        return $this->cache->remember($cacheKey, function () use ($mid) {
            $conn = $this->db->getConnection();
            $sql  = "SELECT c.* FROM montaje m
                     JOIN componente c ON m.ID_Componente = c.ID_Componente
                     WHERE m.ID_Maquina = ?
                     ORDER BY m.fecha DESC";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('s', $mid);
            $stmt->execute();
            $componentes = [];
            while ($row = $stmt->get_result()->fetch_assoc()) {
                $componentes[] = Componente::fromArray($row);
            }
            $stmt->close();
            return $componentes;
        }, $this->ttl);
    }

    // -------------------------------------------------------------------------
    // Escritura + invalidación
    // -------------------------------------------------------------------------

    public function save(MaquinaRecreativa $maquina): void
    {
        $conn = $this->db->getConnection();
        $data = $maquina->toArray();

        $sql = "INSERT INTO MaquinaRecreativa (
                    ID_Maquina, Nombre_Maquina, Tipo, Fecha_Registro, Estado, Etapa,
                    ID_Comercio, ID_Tecnico_Ensamblador, ID_Tecnico_Comprobador, ID_Tecnico_Mantenimiento
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    Nombre_Maquina          = VALUES(Nombre_Maquina),
                    Tipo                    = VALUES(Tipo),
                    Estado                  = VALUES(Estado),
                    Etapa                   = VALUES(Etapa),
                    ID_Tecnico_Ensamblador  = VALUES(ID_Tecnico_Ensamblador),
                    ID_Tecnico_Comprobador  = VALUES(ID_Tecnico_Comprobador),
                    ID_Tecnico_Mantenimiento= VALUES(ID_Tecnico_Mantenimiento)";

        $stmt = $conn->prepare($sql);
        $id   = $data['ID_Maquina'];
        $nom  = $data['Nombre_Maquina'];
        $tip  = $data['Tipo'];
        $fec  = $data['Fecha_Registro'];
        $est  = $data['Estado'];
        $eta  = $data['Etapa'];
        $com  = $data['ID_Comercio'];
        $ens  = $data['ID_Tecnico_Ensamblador'];
        $cob  = $data['ID_Tecnico_Comprobador'];
        $man  = $data['ID_Tecnico_Mantenimiento'];
        $stmt->bind_param('ssssssssss', $id, $nom, $tip, $fec, $est, $eta, $com, $ens, $cob, $man);
        $stmt->execute();
        $stmt->close();

        // Invalidar caché
        $this->cache->delete("maquina:id:{$id}");
        $this->cache->delete("maquinas:estado:{$est}");
        $this->cache->delete("maquinas:etapa:{$eta}");
        $this->cache->delete("maquinas:distribucion");
        if (!empty($ens)) $this->cache->delete("maquinas:ensamblador:{$ens}");
        if (!empty($cob)) $this->cache->delete("maquinas:comprobador:{$cob}");
        if (!empty($man)) $this->cache->delete("maquinas:mantenimiento:{$man}");
        if (!empty($com)) $this->cache->delete("maquinas:operativas_comercio:{$com}");
        $this->cache->delete("maquinas:componentes_montaje:{$id}");
    }
}
