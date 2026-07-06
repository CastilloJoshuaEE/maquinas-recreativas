<?php
/**
 * Infrastructure/Repositories/PDOMaquinaRepository.php
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
use PDO;

class PDOMaquinaRepository implements MaquinaRepository
{
    private Database $db;
    private CacheInterface $cache;
    private int $ttl = 1800;

    public function __construct(Database $db, ?CacheInterface $cache = null)
    {
        $this->db = $db;
        $this->cache = $cache ?? CacheFactory::create();
    }

    // -------------------------------------------------------------------------
    // REGISTRO COMPLETO DE MÁQUINA (usando SP)
    // -------------------------------------------------------------------------

    public function registrarMaquinaCompleta(
        string $nombre,
        string $tipo,
        string $idComercio,
        string $idUsuarioCreador,
        string $idPlaca,
        string $idCarcasa,
        string $idEnsamblador,
        string $idComprobador
    ): string {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("CALL sp_registrar_maquina_completa(?, ?, ?, ?, ?, ?, ?, ?, @id_maquina)");
        $stmt->execute([
            $nombre, $tipo, $idComercio, $idUsuarioCreador,
            $idPlaca, $idCarcasa, $idEnsamblador, $idComprobador
        ]);
        $stmt->closeCursor();

        $result = $conn->query("SELECT @id_maquina as id_maquina");
        $row = $result->fetch(PDO::FETCH_ASSOC);
        $result->closeCursor();
        $this->db->clearPendingResults();

        return $row['id_maquina'] ?? '';
    }

    // -------------------------------------------------------------------------
    // LECTURA con SPs
    // -------------------------------------------------------------------------

    public function findById(Uuid $id): ?MaquinaRecreativa
    {
        $cacheKey = "maquina:id:{$id->value()}";

        return $this->cache->remember($cacheKey, function () use ($id) {
            $conn = $this->db->getConnection();
            $stmt = $conn->prepare("CALL sp_buscar_maquina_por_id(?)");
            $stmt->execute([$id->value()]);
            
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            $this->db->clearPendingResults();
            
            return $data ? MaquinaRecreativa::fromArray($data) : null;
        }, $this->ttl);
    }

    public function findAllWithComercio(): array
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("CALL sp_todas_las_maquinas()");
        $stmt->execute();
        
        $maquinas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        $this->db->clearPendingResults();
        
        return $maquinas;
    }

    public function findByTecnicoEnsamblador(Uuid $idTecnico): array
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("CALL sp_maquinas_por_tecnico_ensamblador(?)");
        $stmt->execute([$idTecnico->value()]);
        
        $maquinas = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $maquinas[] = MaquinaRecreativa::fromArray($row);
        }
        $stmt->closeCursor();
        $this->db->clearPendingResults();
        
        return $maquinas;
    }

    public function findByTecnicoEnsambladorWithComercio(Uuid $idTecnico): array
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("CALL sp_maquinas_por_tecnico_ensamblador(?)");
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
        $stmt->closeCursor();
        $this->db->clearPendingResults();
        
        return $maquinas;
    }

    public function findByTecnicoComprobador(Uuid $idTecnico): array
    {
        $cacheKey = "maquinas:comprobador:{$idTecnico->value()}";

        return $this->cache->remember($cacheKey, function () use ($idTecnico) {
            $conn = $this->db->getConnection();
            $stmt = $conn->prepare("CALL sp_maquinas_por_tecnico_comprobador(?)");
            $stmt->execute([$idTecnico->value()]);
            
            $maquinas = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $maquinas[] = MaquinaRecreativa::fromArray($row);
            }
            $stmt->closeCursor();
            $this->db->clearPendingResults();
            
            return $maquinas;
        }, $this->ttl);
    }

    public function findByTecnicoComprobadorWithComercio(Uuid $idTecnico): array
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("CALL sp_maquinas_por_tecnico_comprobador(?)");
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
        $stmt->closeCursor();
        $this->db->clearPendingResults();
        
        return $maquinas;
    }

    public function findByTecnicoMantenimiento(Uuid $idTecnico): array
    {
        $cacheKey = "maquinas:mantenimiento:{$idTecnico->value()}";

        return $this->cache->remember($cacheKey, function () use ($idTecnico) {
            $conn = $this->db->getConnection();
            $stmt = $conn->prepare("CALL sp_maquinas_por_tecnico_mantenimiento(?)");
            $stmt->execute([$idTecnico->value()]);
            
            $maquinas = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $maquinas[] = MaquinaRecreativa::fromArray($row);
            }
            $stmt->closeCursor();
            $this->db->clearPendingResults();
            
            return $maquinas;
        }, $this->ttl);
    }

    public function findByTecnicoMantenimientoWithComercio(Uuid $idTecnico): array
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("CALL sp_maquinas_por_tecnico_mantenimiento(?)");
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
        $stmt->closeCursor();
        $this->db->clearPendingResults();
        
        return $maquinas;
    }

    public function findByEstado(EstadoMaquina $estado): array
    {
        $cacheKey = "maquinas:estado:{$estado->value()}";

        return $this->cache->remember($cacheKey, function () use ($estado) {
            $conn = $this->db->getConnection();
            $stmt = $conn->prepare("CALL sp_maquinas_por_estado(?)");
            $stmt->execute([$estado->value()]);
            
            $maquinas = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $maquinas[] = MaquinaRecreativa::fromArray($row);
            }
            $stmt->closeCursor();
            $this->db->clearPendingResults();
            
            return $maquinas;
        }, $this->ttl);
    }

    public function findByEstadoWithComercio(EstadoMaquina $estado): array
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("CALL sp_maquinas_por_estado(?)");
        $stmt->execute([$estado->value()]);
        
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
        $stmt->closeCursor();
        $this->db->clearPendingResults();
        
        return $maquinas;
    }

    public function findByEtapa(EtapaMaquina $etapa): array
    {
        $cacheKey = "maquinas:etapa:{$etapa->value()}";

        return $this->cache->remember($cacheKey, function () use ($etapa) {
            $conn = $this->db->getConnection();
            $stmt = $conn->prepare("CALL sp_maquinas_por_etapa(?)");
            $stmt->execute([$etapa->value()]);
            
            $maquinas = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $maquinas[] = MaquinaRecreativa::fromArray($row);
            }
            $stmt->closeCursor();
            $this->db->clearPendingResults();
            
            return $maquinas;
        }, $this->ttl);
    }

    public function findByEtapaWithComercio(EtapaMaquina $etapa): array
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("CALL sp_maquinas_por_etapa(?)");
        $stmt->execute([$etapa->value()]);
        
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
        $stmt->closeCursor();
        $this->db->clearPendingResults();
        
        return $maquinas;
    }

    public function findParaDistribucion(): array
    {
        $cacheKey = "maquinas:distribucion";

        return $this->cache->remember($cacheKey, function () {
            $conn = $this->db->getConnection();
            $stmt = $conn->prepare("CALL sp_maquinas_para_distribucion()");
            $stmt->execute();
            
            $maquinas = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $maquinas[] = MaquinaRecreativa::fromArray($row);
            }
            $stmt->closeCursor();
            $this->db->clearPendingResults();
            
            return $maquinas;
        }, $this->ttl);
    }

    public function findOperativasPorComercio(Comercio $comercio): array
    {
        $cid = $comercio->getId();
        $cacheKey = "maquinas:operativas_comercio:{$cid}";

        return $this->cache->remember($cacheKey, function () use ($cid) {
            $conn = $this->db->getConnection();
            $stmt = $conn->prepare("CALL sp_maquinas_operativas_por_comercio(?)");
            $stmt->execute([$cid]);
            
            $maquinas = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $maquinas[] = MaquinaRecreativa::fromArray($row);
            }
            $stmt->closeCursor();
            $this->db->clearPendingResults();
            
            return $maquinas;
        }, $this->ttl);
    }

    public function getComponentesMontaje(MaquinaRecreativa $maquina): array
    {
        $mid = $maquina->id()->value();
        $cacheKey = "maquinas:componentes_montaje:{$mid}";

        return $this->cache->remember($cacheKey, function () use ($mid) {
            $conn = $this->db->getConnection();
            $stmt = $conn->prepare("CALL sp_componentes_por_maquina(?)");
            $stmt->execute([$mid]);
            
            $componentes = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $componentes[] = Componente::fromArray($row);
            }
            $stmt->closeCursor();
            $this->db->clearPendingResults();
            
            return $componentes;
        }, $this->ttl);
    }

    public function getComponentesEnUsoPorMaquina(Uuid $idMaquina): array
    {
        $mid = $idMaquina->value();
        $cacheKey = "maquinas:componentes_en_uso:{$mid}";

        return $this->cache->remember($cacheKey, function () use ($mid) {
            $conn = $this->db->getConnection();
            $stmt = $conn->prepare("CALL sp_componentes_en_uso_por_maquina(?)");
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
            $stmt->closeCursor();
            $this->db->clearPendingResults();
            
            return $componentes;
        }, 600);
    }

    // -------------------------------------------------------------------------
    // ESCRITURA - Usando SPs
    // -------------------------------------------------------------------------

    public function save(MaquinaRecreativa $maquina): void
    {
        $conn = $this->db->getConnection();
        $data = $maquina->toArray();

        $existing = $this->findById($maquina->id());

        if ($existing) {
            $stmt = $conn->prepare("CALL sp_actualizar_maquina(?, ?, ?, ?, ?,?)");
            $stmt->execute([
                $data['ID_Maquina'],
                $data['Nombre_Maquina'],
                $data['Tipo'],
                $data['ID_Comercio'],
                $data['Estado'],
                $data['Etapa']
            ]);
        } else {
            $stmt = $conn->prepare("CALL sp_insertar_maquina(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $data['ID_Maquina'],
                $data['Nombre_Maquina'],
                $data['Tipo'],
                $data['Fecha_Registro'],
                $data['Estado'],
                $data['Etapa'],
                $data['ID_Comercio'],
                $data['ID_Tecnico_Ensamblador'],
                $data['ID_Tecnico_Comprobador'],
                $data['ID_Tecnico_Mantenimiento']
            ]);
        }
        $stmt->closeCursor();
        $this->db->clearPendingResults();

        // Invalidar caché
        $id = $data['ID_Maquina'];
        $this->cache->delete("maquina:id:{$id}");
        $this->cache->delete("maquinas:estado:{$data['Estado']}");
        $this->cache->delete("maquinas:etapa:{$data['Etapa']}");
        $this->cache->delete("maquinas:distribucion");
        if (!empty($data['ID_Tecnico_Ensamblador'])) {
            $this->cache->delete("maquinas:ensamblador:{$data['ID_Tecnico_Ensamblador']}");
        }
        if (!empty($data['ID_Tecnico_Comprobador'])) {
            $this->cache->delete("maquinas:comprobador:{$data['ID_Tecnico_Comprobador']}");
        }
        if (!empty($data['ID_Tecnico_Mantenimiento'])) {
            $this->cache->delete("maquinas:mantenimiento:{$data['ID_Tecnico_Mantenimiento']}");
        }
        if (!empty($data['ID_Comercio'])) {
            $this->cache->delete("maquinas:operativas_comercio:{$data['ID_Comercio']}");
        }
        $this->cache->delete("maquinas:componentes_montaje:{$id}");
    }

    public function actualizarEstadoYEtapa(Uuid $idMaquina, string $estado, string $etapa): bool
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("CALL sp_actualizar_estado_maquina(?, ?, ?)");
        $result = $stmt->execute([$idMaquina->value(), $estado, $etapa]);
        $stmt->closeCursor();
        $this->db->clearPendingResults();

        $this->cache->delete("maquina:id:{$idMaquina->value()}");
        $this->cache->delete("maquinas:estado:{$estado}");
        $this->cache->delete("maquinas:etapa:{$etapa}");

        return $result;
    }

    public function asignarTecnicoMantenimiento(Uuid $idMaquina, Uuid $idTecnico): bool
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("CALL sp_asignar_tecnico_mantenimiento(?, ?)");
        $result = $stmt->execute([$idMaquina->value(), $idTecnico->value()]);
        $stmt->closeCursor();
        $this->db->clearPendingResults();

        $this->cache->delete("maquina:id:{$idMaquina->value()}");
        $this->cache->delete("maquinas:mantenimiento:{$idTecnico->value()}");

        return $result;
    }
}