<?php
/**
 * Infrastructure/Repositories/MySQLMaquinaRepository.php
 * Migrado a PDO.
 */
namespace maquinas_recreativas\Infrastructure\Repositories;

use PDO;
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

    public function findById(Uuid $id): ?MaquinaRecreativa
    {
        $cacheKey = "maquina:id:{$id->value()}";
        return $this->cache->remember($cacheKey, function () use ($id) {
            $conn = $this->db->getConnection();
            $stmt = $conn->prepare("SELECT * FROM MaquinaRecreativa WHERE ID_Maquina = ?");
            $stmt->execute([$id->value()]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $data ? MaquinaRecreativa::fromArray($data) : null;
        }, $this->ttl);
    }

    public function findByTecnicoEnsamblador(Uuid $idTecnico): array
    {
        $conn = $this->db->getConnection();
        $sql = "SELECT m.*, c.Nombre as NombreComercio, c.Direccion as DireccionComercio
                FROM MaquinaRecreativa m
                LEFT JOIN Comercio c ON m.ID_Comercio = c.ID_Comercio
                WHERE m.ID_Tecnico_Ensamblador = ?
                ORDER BY m.Fecha_Registro DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$idTecnico->value()]);
        $maquinas = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $maquinas[] = MaquinaRecreativa::fromArray($row);
        }
        return $maquinas;
    }

    public function findByTecnicoEnsambladorWithComercio(Uuid $idTecnico): array
    {
        $conn = $this->db->getConnection();
        $sql = "SELECT
                    m.ID_Maquina, m.Nombre_Maquina, m.Tipo, m.Estado, m.Etapa,
                    m.ID_Comercio, m.ID_Tecnico_Ensamblador, m.ID_Tecnico_Comprobador,
                    m.Fecha_Registro, c.Nombre as NombreComercio, c.Direccion as DireccionComercio
                FROM MaquinaRecreativa m
                LEFT JOIN Comercio c ON m.ID_Comercio = c.ID_Comercio
                WHERE m.ID_Tecnico_Ensamblador = ?
                ORDER BY m.Fecha_Registro DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$idTecnico->value()]);
        $maquinas = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $maquinas[] = [
                'ID_Maquina' => $row['ID_Maquina'],
                'Nombre_Maquina' => $row['Nombre_Maquina'],
                'Tipo' => $row['Tipo'],
                'Estado' => $row['Estado'],
                'Etapa' => $row['Etapa'],
                'ID_Comercio' => $row['ID_Comercio'],
                'ID_Tecnico_Ensamblador' => $row['ID_Tecnico_Ensamblador'],
                'ID_Tecnico_Comprobador' => $row['ID_Tecnico_Comprobador'],
                'Fecha_Registro' => $row['Fecha_Registro'],
                'NombreComercio' => $row['NombreComercio'] ?? '',
                'DireccionComercio' => $row['DireccionComercio'] ?? ''
            ];
        }
        error_log("findByTecnicoEnsambladorWithComercio: " . count($maquinas) . " máquinas encontradas");
        return $maquinas;
    }

    public function findByTecnicoComprobadorWithComercio(Uuid $idTecnico): array
    {
        $conn = $this->db->getConnection();
        $sql = "SELECT
                    m.ID_Maquina, m.Nombre_Maquina, m.Tipo, m.Estado, m.Etapa,
                    m.ID_Comercio, m.ID_Tecnico_Ensamblador, m.ID_Tecnico_Comprobador,
                    m.Fecha_Registro, c.Nombre as NombreComercio, c.Direccion as DireccionComercio
                FROM MaquinaRecreativa m
                LEFT JOIN Comercio c ON m.ID_Comercio = c.ID_Comercio
                WHERE m.ID_Tecnico_Comprobador = ?
                  AND m.Estado = 'Comprobandose'
                ORDER BY m.Fecha_Registro DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$idTecnico->value()]);
        $maquinas = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $maquinas[] = [
                'ID_Maquina' => $row['ID_Maquina'],
                'Nombre_Maquina' => $row['Nombre_Maquina'],
                'Tipo' => $row['Tipo'],
                'Estado' => $row['Estado'],
                'Etapa' => $row['Etapa'],
                'ID_Comercio' => $row['ID_Comercio'],
                'ID_Tecnico_Ensamblador' => $row['ID_Tecnico_Ensamblador'],
                'ID_Tecnico_Comprobador' => $row['ID_Tecnico_Comprobador'],
                'Fecha_Registro' => $row['Fecha_Registro'],
                'NombreComercio' => $row['NombreComercio'] ?? '',
                'DireccionComercio' => $row['DireccionComercio'] ?? ''
            ];
        }
        error_log("findByTecnicoComprobadorWithComercio: " . count($maquinas) . " máquinas encontradas");
        return $maquinas;
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
            $stmt->execute([$idTecnico->value()]);
            $maquinas = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $maquinas[] = MaquinaRecreativa::fromArray($row);
            }
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
            $stmt->execute([$idTecnico->value()]);
            $maquinas = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $maquinas[] = MaquinaRecreativa::fromArray($row);
            }
            return $maquinas;
        }, $this->ttl);
    }

    public function findByEstado(EstadoMaquina $estado): array
    {
        $cacheKey = "maquinas:estado:{$estado->value()}";
        return $this->cache->remember($cacheKey, function () use ($estado) {
            $conn = $this->db->getConnection();
            $stmt = $conn->prepare("SELECT * FROM MaquinaRecreativa WHERE Estado = ? ORDER BY Fecha_Registro DESC");
            $stmt->execute([$estado->value()]);
            $maquinas = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $maquinas[] = MaquinaRecreativa::fromArray($row);
            }
            return $maquinas;
        }, $this->ttl);
    }

    public function findByEtapa(EtapaMaquina $etapa): array
    {
        $cacheKey = "maquinas:etapa:{$etapa->value()}";
        return $this->cache->remember($cacheKey, function () use ($etapa) {
            $conn = $this->db->getConnection();
            $stmt = $conn->prepare("SELECT * FROM MaquinaRecreativa WHERE Etapa = ? ORDER BY Fecha_Registro DESC");
            $stmt->execute([$etapa->value()]);
            $maquinas = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $maquinas[] = MaquinaRecreativa::fromArray($row);
            }
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
        $stmt = $conn->prepare("SELECT * FROM MaquinaRecreativa WHERE Etapa = ? AND Estado = ? ORDER BY Fecha_Registro DESC");
        $stmt->execute([$etapa->value(), $estado->value()]);
        $maquinas = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $maquinas[] = MaquinaRecreativa::fromArray($row);
        }
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
            $stmt->execute([$cid]);
            $maquinas = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $maquinas[] = MaquinaRecreativa::fromArray($row);
            }
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
            $stmt->execute([$mid]);
            $componentes = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $componentes[] = Componente::fromArray($row);
            }
            return $componentes;
        }, $this->ttl);
    }

    public function findByEtapaWithComercio(EtapaMaquina $etapa): array
    {
        $conn = $this->db->getConnection();
        $sql = "SELECT
                    m.ID_Maquina, m.Nombre_Maquina, m.Tipo, m.Estado, m.Etapa,
                    m.ID_Comercio, m.ID_Tecnico_Ensamblador, m.ID_Tecnico_Comprobador,
                    m.Fecha_Registro, c.Nombre as NombreComercio, c.Direccion as DireccionComercio
                FROM MaquinaRecreativa m
                LEFT JOIN Comercio c ON m.ID_Comercio = c.ID_Comercio
                WHERE m.Etapa = ?
                ORDER BY m.Fecha_Registro DESC";
        $stmt = $conn->prepare($sql);
        $v = $etapa->value();
        $stmt->execute([$v]);
        $maquinas = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $maquinas[] = [
                'ID_Maquina' => $row['ID_Maquina'],
                'Nombre_Maquina' => $row['Nombre_Maquina'],
                'Tipo' => $row['Tipo'],
                'Estado' => $row['Estado'],
                'Etapa' => $row['Etapa'],
                'ID_Comercio' => $row['ID_Comercio'],
                'ID_Tecnico_Ensamblador' => $row['ID_Tecnico_Ensamblador'],
                'ID_Tecnico_Comprobador' => $row['ID_Tecnico_Comprobador'],
                'Fecha_Registro' => $row['Fecha_Registro'],
                'NombreComercio' => $row['NombreComercio'] ?? '',
                'DireccionComercio' => $row['DireccionComercio'] ?? ''
            ];
        }
        error_log("findByEtapaWithComercio: etapa={$v}, encontradas=" . count($maquinas));
        return $maquinas;
    }

    public function findByEstadoWithComercio(EstadoMaquina $estado): array
    {
        $conn = $this->db->getConnection();
        $sql = "SELECT
                    m.ID_Maquina, m.Nombre_Maquina, m.Tipo, m.Estado, m.Etapa,
                    m.ID_Comercio, m.ID_Tecnico_Ensamblador, m.ID_Tecnico_Comprobador,
                    m.Fecha_Registro, c.Nombre as NombreComercio, c.Direccion as DireccionComercio
                FROM MaquinaRecreativa m
                LEFT JOIN Comercio c ON m.ID_Comercio = c.ID_Comercio
                WHERE m.Estado = ?
                ORDER BY m.Fecha_Registro DESC";
        $stmt = $conn->prepare($sql);
        $v = $estado->value();
        $stmt->execute([$v]);
        $maquinas = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $maquinas[] = [
                'ID_Maquina' => $row['ID_Maquina'],
                'Nombre_Maquina' => $row['Nombre_Maquina'],
                'Tipo' => $row['Tipo'],
                'Estado' => $row['Estado'],
                'Etapa' => $row['Etapa'],
                'ID_Comercio' => $row['ID_Comercio'],
                'ID_Tecnico_Ensamblador' => $row['ID_Tecnico_Ensamblador'],
                'ID_Tecnico_Comprobador' => $row['ID_Tecnico_Comprobador'],
                'Fecha_Registro' => $row['Fecha_Registro'],
                'NombreComercio' => $row['NombreComercio'] ?? '',
                'DireccionComercio' => $row['DireccionComercio'] ?? ''
            ];
        }
        error_log("findByEstadoWithComercio: estado={$v}, encontradas=" . count($maquinas));
        return $maquinas;
    }

    public function findByTecnicoMantenimientoWithComercio(Uuid $idTecnico): array
    {
        $conn = $this->db->getConnection();
        $sql = "SELECT
                    m.ID_Maquina, m.Nombre_Maquina, m.Tipo, m.Estado, m.Etapa,
                    m.ID_Comercio, m.ID_Tecnico_Ensamblador, m.ID_Tecnico_Comprobador,
                    m.Fecha_Registro, c.Nombre as NombreComercio, c.Direccion as DireccionComercio
                FROM MaquinaRecreativa m
                LEFT JOIN Comercio c ON m.ID_Comercio = c.ID_Comercio
                WHERE m.ID_Tecnico_Mantenimiento = ?
                  AND m.Estado = 'No operativa'
                ORDER BY m.Fecha_Registro DESC";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            error_log("findByTecnicoMantenimientoWithComercio: error preparing statement");
            return [];
        }
        $stmt->execute([$idTecnico->value()]);
        $maquinas = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $maquinas[] = [
                'ID_Maquina' => $row['ID_Maquina'],
                'Nombre_Maquina' => $row['Nombre_Maquina'],
                'Tipo' => $row['Tipo'],
                'Estado' => $row['Estado'],
                'Etapa' => $row['Etapa'],
                'ID_Comercio' => $row['ID_Comercio'],
                'ID_Tecnico_Ensamblador' => $row['ID_Tecnico_Ensamblador'],
                'ID_Tecnico_Comprobador' => $row['ID_Tecnico_Comprobador'],
                'Fecha_Registro' => $row['Fecha_Registro'],
                'NombreComercio' => $row['NombreComercio'] ?? '',
                'DireccionComercio' => $row['DireccionComercio'] ?? ''
            ];
        }
        error_log("findByTecnicoMantenimientoWithComercio: " . count($maquinas) . " máquinas encontradas");
        return $maquinas;
    }

    public function save(MaquinaRecreativa $maquina): void
    {
        $conn = $this->db->getConnection();
        $data = $maquina->toArray();

        $cols = ['ID_Maquina','Nombre_Maquina','Tipo','Fecha_Registro','Estado','Etapa',
                 'ID_Comercio','ID_Tecnico_Ensamblador','ID_Tecnico_Comprobador','ID_Tecnico_Mantenimiento'];

        if ($this->db->isPostgres()) {
            $sql = "INSERT INTO MaquinaRecreativa (" . implode(',', $cols) . ")
                    VALUES (?,?,?,?,?,?,?,?,?,?)
                    ON CONFLICT (ID_Maquina) DO UPDATE SET
                        Nombre_Maquina=EXCLUDED.Nombre_Maquina,
                        Tipo=EXCLUDED.Tipo,
                        Estado=EXCLUDED.Estado,
                        Etapa=EXCLUDED.Etapa,
                        ID_Tecnico_Ensamblador=EXCLUDED.ID_Tecnico_Ensamblador,
                        ID_Tecnico_Comprobador=EXCLUDED.ID_Tecnico_Comprobador,
                        ID_Tecnico_Mantenimiento=EXCLUDED.ID_Tecnico_Mantenimiento";
        } else {
            $sql = "INSERT INTO MaquinaRecreativa (" . implode(',', $cols) . ")
                    VALUES (?,?,?,?,?,?,?,?,?,?)
                    ON DUPLICATE KEY UPDATE
                        Nombre_Maquina          = VALUES(Nombre_Maquina),
                        Tipo                    = VALUES(Tipo),
                        Estado                  = VALUES(Estado),
                        Etapa                   = VALUES(Etapa),
                        ID_Tecnico_Ensamblador  = VALUES(ID_Tecnico_Ensamblador),
                        ID_Tecnico_Comprobador  = VALUES(ID_Tecnico_Comprobador),
                        ID_Tecnico_Mantenimiento= VALUES(ID_Tecnico_Mantenimiento)";
        }

        $stmt = $conn->prepare($sql);
        $id  = $data['ID_Maquina'];
        $est = $data['Estado'];
        $eta = $data['Etapa'];
        $com = $data['ID_Comercio'];
        $ens = $data['ID_Tecnico_Ensamblador'];
        $cob = $data['ID_Tecnico_Comprobador'];
        $man = $data['ID_Tecnico_Mantenimiento'];

        $stmt->execute([
            $id, $data['Nombre_Maquina'], $data['Tipo'], $data['Fecha_Registro'],
            $est, $eta, $com, $ens, $cob, $man
        ]);

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

    public function findAllWithComercio(): array
    {
        $conn = $this->db->getConnection();

        $sql = "SELECT
                    m.ID_Maquina, m.Nombre_Maquina, m.Tipo, m.Estado, m.Etapa,
                    m.ID_Comercio, m.ID_Tecnico_Ensamblador, m.ID_Tecnico_Comprobador,
                    m.Fecha_Registro, c.Nombre AS NombreComercio, c.Direccion AS DireccionComercio
                FROM MaquinaRecreativa m
                LEFT JOIN Comercio c ON m.ID_Comercio = c.ID_Comercio
                ORDER BY m.Fecha_Registro DESC";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            error_log("findAllWithComercio - prepare failed");
            return [];
        }
        if (!$stmt->execute()) {
            error_log("findAllWithComercio - execute failed");
            return [];
        }

        $maquinas = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $maquinas[] = [
                'ID_Maquina'              => $row['ID_Maquina'],
                'Nombre_Maquina'          => $row['Nombre_Maquina'],
                'Tipo'                    => $row['Tipo'],
                'Estado'                  => $row['Estado'],
                'Etapa'                   => $row['Etapa'],
                'ID_Comercio'             => $row['ID_Comercio'],
                'ID_Tecnico_Ensamblador'  => $row['ID_Tecnico_Ensamblador'],
                'ID_Tecnico_Comprobador'  => $row['ID_Tecnico_Comprobador'],
                'Fecha_Registro'          => $row['Fecha_Registro'],
                'NombreComercio'          => $row['NombreComercio']    ?? '',
                'DireccionComercio'       => $row['DireccionComercio'] ?? '',
            ];
        }

        error_log("findAllWithComercio: " . count($maquinas) . " máquinas encontradas");
        return $maquinas;
    }

    public function getComponentesEnUsoPorMaquina(Uuid $idMaquina): array
    {
        $mid = $idMaquina->value();
        $cacheKey = "maquinas:componentes_en_uso:{$mid}";

        return $this->cache->remember($cacheKey, function () use ($mid) {
            $conn = $this->db->getConnection();
            $sql = "SELECT
                        c.ID_Componente, c.tipo, c.nombre, c.precio, cu.fecha_asignacion
                    FROM componente_usuario cu
                    INNER JOIN componente c ON cu.ID_Componente = c.ID_Componente
                    WHERE cu.ID_Maquina = ?
                    AND cu.fecha_liberacion IS NULL
                    ORDER BY cu.fecha_asignacion DESC";

            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                error_log("Error preparando consulta getComponentesEnUsoPorMaquina");
                return [];
            }
            $stmt->execute([$mid]);

            $componentes = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $componentes[] = [
                    'ID_Componente' => $row['ID_Componente'],
                    'tipo' => $row['tipo'],
                    'nombre' => $row['nombre'],
                    'precio' => (float)$row['precio'],
                    'fecha_asignacion' => $row['fecha_asignacion']
                ];
            }
            return $componentes;
        }, 600);
    }
}