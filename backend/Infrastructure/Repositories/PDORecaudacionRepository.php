<?php
/**
 * Infrastructure/Repositories/PDORecaudacionRepository.php
 */
namespace maquinas_recreativas\Infrastructure\Repositories;

use maquinas_recreativas\Domain\Recaudacion\Recaudacion;
use maquinas_recreativas\Domain\Recaudacion\InformeRecaudacion;
use maquinas_recreativas\Domain\Recaudacion\DetalleInforme;
use maquinas_recreativas\Domain\Recaudacion\RecaudacionRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Domain\Comercio\Comercio;
use maquinas_recreativas\Infrastructure\Database\Database;
use maquinas_recreativas\Infrastructure\Cache\CacheInterface;
use maquinas_recreativas\Infrastructure\Cache\CacheFactory;
use PDO;

class PDORecaudacionRepository implements RecaudacionRepository
{
    private Database $db;
    private CacheInterface $cache;
    private int $ttl = 300;

    public function __construct(Database $db, ?CacheInterface $cache = null)
    {
        $this->db = $db;
        $this->cache = $cache ?? CacheFactory::create();
    }

    public function findById(Uuid $id): ?Recaudacion
    {
        $cacheKey = "recaudacion:id:{$id->value()}";
        return $this->cache->remember($cacheKey, function () use ($id) {
            $conn = $this->db->getConnection();
            $stmt = $conn->prepare("CALL sp_buscar_recaudacion_por_id(?)");
            $stmt->execute([$id->value()]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            $this->db->clearPendingResults();
            
            return $data ? Recaudacion::fromArray($data) : null;
        }, $this->ttl);
    }

    public function findInformeByRecaudacion(Uuid $idRecaudacion): ?InformeRecaudacion
    {
        $cacheKey = "recaudacion:informe:{$idRecaudacion->value()}";
        return $this->cache->remember($cacheKey, function () use ($idRecaudacion) {
            $conn = $this->db->getConnection();
            $stmt = $conn->prepare("CALL sp_obtener_informe_por_recaudacion(?)");
            $stmt->execute([$idRecaudacion->value()]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            $this->db->clearPendingResults();
            
            return $data ? InformeRecaudacion::fromArray($data) : null;
        }, $this->ttl);
    }

    public function findDetallesByInforme(Uuid $idInforme): array
    {
        $cacheKey = "recaudacion:detalles:{$idInforme->value()}";
        return $this->cache->remember($cacheKey, function () use ($idInforme) {
            $conn = $this->db->getConnection();
            $stmt = $conn->prepare("CALL sp_detalles_informe(?)");
            $stmt->execute([$idInforme->value()]);
            
            $detalles = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            $this->db->clearPendingResults();
            
            return $detalles;
        }, $this->ttl);
    }

    public function findMaquinasRecaudacion(): array
    {
        $cacheKey = "recaudacion:maquinas_operativas";
        return $this->cache->remember($cacheKey, function () {
            $conn = $this->db->getConnection();
            $stmt = $conn->prepare("CALL sp_maquinas_operativas_recaudacion()");
            $stmt->execute();
            
            $maquinas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            $this->db->clearPendingResults();
            
            return $maquinas;
        }, 600);
    }

    public function save(Recaudacion $recaudacion): void
    {
        $conn = $this->db->getConnection();
        $data = $recaudacion->toArray();

        $fecha = !empty($data['fecha']) ? $data['fecha'] : date('Y-m-d H:i:s');
        $detalle = (string)($data['detalle'] ?? '');

        $existing = $this->findById($recaudacion->id());

        if ($existing) {
            $stmt = $conn->prepare("CALL sp_actualizar_recaudacion(?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $data['ID_Recaudacion'],
                $data['Tipo_Comercio'],
                $data['ID_Maquina'],
                $data['Monto_Total'],
                $data['Monto_Empresa'],
                $data['Monto_Comercio'],
                $data['Porcentaje_Comercio'],
                $fecha,
                $detalle
            ]);
        } else {
            $stmt = $conn->prepare("CALL sp_insertar_recaudacion(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $data['ID_Recaudacion'],
                $data['Tipo_Comercio'],
                $data['ID_Maquina'],
                $data['ID_Usuario'],
                $data['Monto_Total'],
                $data['Monto_Empresa'],
                $data['Monto_Comercio'],
                $data['Porcentaje_Comercio'],
                $fecha,
                $detalle
            ]);
        }
        $stmt->closeCursor();
        $this->db->clearPendingResults();

        $this->cache->delete("recaudacion:id:{$data['ID_Recaudacion']}");
        $this->cache->delete("recaudacion:nombre_maquina:{$data['ID_Maquina']}");
        $this->invalidateListados();
    }

    public function saveInforme(InformeRecaudacion $informe): void
    {
        $conn = $this->db->getConnection();
        $data = $informe->toArray();

        $stmt = $conn->prepare("CALL sp_guardar_informe_recaudacion(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['ID_Informe'],
            $data['ID_Recaudacion'],
            $data['CI_Usuario'],
            $data['Nombre_Maquina'],
            $data['ID_Comercio'],
            $data['Nombre_Comercio'],
            $data['Direccion_Comercio'],
            $data['Telefono_Comercio'],
            $data['Pago_Ensamblador'],
            $data['Pago_Comprobador'],
            $data['Pago_Mantenimiento'],
            $data['empresa_nombre'],
            $data['empresa_descripcion']
        ]);
        $stmt->closeCursor();
        $this->db->clearPendingResults();

        $this->cache->delete("recaudacion:informe:{$data['ID_Recaudacion']}");
    }

    public function saveDetalle(DetalleInforme $detalle): void
    {
        $conn = $this->db->getConnection();
        $data = $detalle->toArray();

        $stmt = $conn->prepare("CALL sp_guardar_detalle_informe(?, ?, ?)");
        $stmt->execute([$data['ID_Informe_Detalle'], $data['ID_Informe'], $data['ID_Componente']]);
        $stmt->closeCursor();
        $this->db->clearPendingResults();

        $this->cache->delete("recaudacion:detalles:{$data['ID_Informe']}");
    }

    public function delete(Uuid $id): bool
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("CALL sp_eliminar_recaudacion(?)");
        $result = $stmt->execute([$id->value()]);
        $stmt->closeCursor();
        $this->db->clearPendingResults();

        $this->cache->delete("recaudacion:id:{$id->value()}");
        $this->cache->delete("recaudacion:informe:{$id->value()}");
        $this->invalidateListados();

        return $result;
    }

    public function findAll(array $filters = [], int $limit = 100, int $offset = 0): array
    {
        $cacheKey = "recaudaciones:all:" . md5(serialize([$filters, $limit, $offset]));
        return $this->cache->remember($cacheKey, function () use ($filters, $limit, $offset) {
            $conn = $this->db->getConnection();
            $fechaInicio = $filters['fechaInicio'] ?? null;
            $fechaFin = $filters['fechaFin'] ?? null;
            $idMaquina = $filters['idMaquina'] ?? null;
            $tipoComercio = $filters['tipoComercio'] ?? null;

            $stmt = $conn->prepare("CALL sp_listar_recaudaciones(?, ?, ?, ?, ?, ?)");
            $stmt->execute([$fechaInicio, $fechaFin, $idMaquina, $tipoComercio, $limit, $offset]);
            
            $recaudaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            $this->db->clearPendingResults();
            
            return $recaudaciones;
        }, $this->ttl);
    }

    public function findResumenByTipoComercio(?int $limit = null): array
    {
        $cacheKey = "recaudaciones:resumen:" . ($limit ?? 'all');
        return $this->cache->remember($cacheKey, function () use ($limit) {
            $conn = $this->db->getConnection();
            $stmt = $conn->prepare("CALL sp_resumen_recaudaciones(?)");
            $stmt->execute([$limit]);
            
            $resumen = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            $this->db->clearPendingResults();
            
            if (empty($resumen)) {
                $resumen[] = [
                    'Tipo_Comercio' => 'Sin datos',
                    'TotalRecaudaciones' => 0,
                    'TotalRecaudado' => 0,
                    'TotalEmpresa' => 0,
                    'TotalComercio' => 0
                ];
            }
            return $resumen;
        }, $this->ttl);
    }

    public function findMaquinasOperativasPorComercio(Comercio $comercio): array
    {
        $cid = $comercio->getId();
        $cacheKey = "recaudacion:maquinas_comercio:{$cid}";
        return $this->cache->remember($cacheKey, function () use ($cid) {
            $conn = $this->db->getConnection();
            $stmt = $conn->prepare("CALL sp_maquinas_operativas_por_comercio(?)");
            $stmt->execute([$cid]);
            
            $maquinas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            $this->db->clearPendingResults();
            
            return $maquinas;
        }, 600);
    }

    public function findNombreMaquinaById(Uuid $idMaquina): ?string
    {
        $cacheKey = "recaudacion:nombre_maquina:{$idMaquina->value()}";
        return $this->cache->remember($cacheKey, function () use ($idMaquina) {
            $conn = $this->db->getConnection();
            $stmt = $conn->prepare("SELECT Nombre_Maquina FROM MaquinaRecreativa WHERE ID_Maquina = ?");
            $stmt->execute([$idMaquina->value()]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            $this->db->clearPendingResults();
            
            return $row ? $row['Nombre_Maquina'] : null;
        }, $this->ttl);
    }
public function findTecnicosByRecaudacion(Uuid $idRecaudacion): array
{
    $cacheKey = "recaudacion:tecnicos:{$idRecaudacion->value()}";
    return $this->cache->remember($cacheKey, function () use ($idRecaudacion) {
        $conn = $this->db->getConnection();
        
        // Obtener los técnicos de la máquina asociada a la recaudación
        $stmt = $conn->prepare("
            SELECT 
                te.ID_Usuario AS id_ensamblador,
                te.nombre AS nombre_ensamblador,
                te.apellido AS apellido_ensamblador,
                tc.ID_Usuario AS id_comprobador,
                tc.nombre AS nombre_comprobador,
                tc.apellido AS apellido_comprobador,
                tm.ID_Usuario AS id_mantenimiento,
                tm.nombre AS nombre_mantenimiento,
                tm.apellido AS apellido_mantenimiento
            FROM recaudaciones r
            INNER JOIN MaquinaRecreativa m ON r.ID_Maquina = m.ID_Maquina
            LEFT JOIN usuario te ON m.ID_Tecnico_Ensamblador = te.ID_Usuario
            LEFT JOIN usuario tc ON m.ID_Tecnico_Comprobador = tc.ID_Usuario
            LEFT JOIN usuario tm ON m.ID_Tecnico_Mantenimiento = tm.ID_Usuario
            WHERE r.ID_Recaudacion = ?
        ");
        $stmt->execute([$idRecaudacion->value()]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        $this->db->clearPendingResults();
        
        // Formatear la salida
        $tecnicos = [];
        
        if ($result) {
            if (!empty($result['id_ensamblador'])) {
                $tecnicos[] = [
                    'id' => $result['id_ensamblador'],
                    'nombre' => $result['nombre_ensamblador'] ?? '',
                    'apellido' => $result['apellido_ensamblador'] ?? '',
                    'especialidad' => 'Ensamblador'
                ];
            }
            if (!empty($result['id_comprobador'])) {
                $tecnicos[] = [
                    'id' => $result['id_comprobador'],
                    'nombre' => $result['nombre_comprobador'] ?? '',
                    'apellido' => $result['apellido_comprobador'] ?? '',
                    'especialidad' => 'Comprobador'
                ];
            }
            if (!empty($result['id_mantenimiento'])) {
                $tecnicos[] = [
                    'id' => $result['id_mantenimiento'],
                    'nombre' => $result['nombre_mantenimiento'] ?? '',
                    'apellido' => $result['apellido_mantenimiento'] ?? '',
                    'especialidad' => 'Mantenimiento'
                ];
            }
        }
        
        return $tecnicos;
    }, $this->ttl);
}
    private function invalidateListados(): void
    {
        $this->cache->delete("recaudaciones:resumen:all");
        $this->cache->delete("recaudacion:maquinas_operativas");
        
        if ($this->cache instanceof \maquinas_recreativas\Infrastructure\Cache\RedisCache) {
            $this->cache->deleteByPattern("recaudaciones:all:*");
            $this->cache->deleteByPattern("recaudaciones:resumen:*");
            $this->cache->deleteByPattern("recaudacion:maquinas_comercio:*");
            $this->cache->deleteByPattern("recaudacion:nombre_maquina:*");
        }
    }
}