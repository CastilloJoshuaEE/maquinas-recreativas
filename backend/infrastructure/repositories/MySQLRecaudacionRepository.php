<?php
/**
 * Infrastructure/Repositories/MySQLRecaudacionRepository.php
 * TTL: 300 s — crece constantemente.
 */
namespace maquinas_recreativas\Infrastructure\Repositories;

use maquinas_recreativas\Domain\Recaudacion\Recaudacion;
use maquinas_recreativas\Domain\Recaudacion\InformeRecaudacion;
use maquinas_recreativas\Domain\Recaudacion\DetalleInforme;
use maquinas_recreativas\Domain\Recaudacion\RecaudacionRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Domain\Maquina\MaquinaRecreativa;
use maquinas_recreativas\Domain\Comercio\Comercio;
use maquinas_recreativas\Infrastructure\Database\Database;
use maquinas_recreativas\Infrastructure\Cache\CacheInterface;
use maquinas_recreativas\Infrastructure\Cache\CacheFactory;

class MySQLRecaudacionRepository implements RecaudacionRepository
{
    private Database       $db;
    private CacheInterface $cache;
    private int            $ttl = 300;

    public function __construct(Database $db, ?CacheInterface $cache = null)
    {
        $this->db    = $db;
        $this->cache = $cache ?? CacheFactory::create();
    }

    public function findById(Uuid $id): ?Recaudacion
    {
        $cacheKey = "recaudacion:id:{$id->value()}";
        return $this->cache->remember($cacheKey, function () use ($id) {
            $conn = $this->db->getConnection();
            $stmt = $conn->prepare("SELECT * FROM recaudaciones WHERE ID_Recaudacion=?");
            $v    = $id->value(); $stmt->bind_param('s', $v);
            $stmt->execute();
            $data = $stmt->get_result()->fetch_assoc(); $stmt->close();
            return $data ? Recaudacion::fromArray($data) : null;
        }, $this->ttl);
    }

    public function findInformeByRecaudacion(Uuid $idRecaudacion): ?InformeRecaudacion
    {
        $cacheKey = "recaudacion:informe:{$idRecaudacion->value()}";
        return $this->cache->remember($cacheKey, function () use ($idRecaudacion) {
            $conn = $this->db->getConnection();
            $stmt = $conn->prepare("SELECT * FROM informes_recaudacion WHERE ID_Recaudacion=?");
            $v    = $idRecaudacion->value(); $stmt->bind_param('s', $v);
            $stmt->execute();
            $data = $stmt->get_result()->fetch_assoc(); $stmt->close();
            return $data ? InformeRecaudacion::fromArray($data) : null;
        }, $this->ttl);
    }

    public function findDetallesByInforme(Uuid $idInforme): array
    {
        $cacheKey = "recaudacion:detalles:{$idInforme->value()}";
        return $this->cache->remember($cacheKey, function () use ($idInforme) {
            $conn = $this->db->getConnection();
            $sql  = "SELECT c.* FROM informe_detalle id JOIN componente c ON id.ID_Componente=c.ID_Componente WHERE id.ID_Informe=?";
            $stmt = $conn->prepare($sql);
            $v    = $idInforme->value(); $stmt->bind_param('s', $v);
            $stmt->execute();
            $detalles = [];
            while ($row = $stmt->get_result()->fetch_assoc()) $detalles[] = $row;
            $stmt->close();
            return $detalles;
        }, $this->ttl);
    }

    public function findMaquinasRecaudacion(): array
    {
        $cacheKey = "recaudacion:maquinas_operativas";
        return $this->cache->remember($cacheKey, function () {
            $conn = $this->db->getConnection();
            $sql  = "SELECT m.*,c.Nombre as NombreComercio,c.Direccion as DireccionComercio,
                            c.Telefono as TelefonoComercio,c.Tipo as TipoComercio
                     FROM MaquinaRecreativa m LEFT JOIN Comercio c ON m.ID_Comercio=c.ID_Comercio
                     WHERE m.Etapa='Recaudacion' AND m.Estado='Operativa' ORDER BY m.Fecha_Registro DESC";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $maquinas = [];
            while ($row = $stmt->get_result()->fetch_assoc()) $maquinas[] = $row;
            $stmt->close();
            return $maquinas;
        }, 600);
    }

    public function save(Recaudacion $recaudacion): void
    {
        $conn = $this->db->getConnection();
        $data = $recaudacion->toArray();
        $sql  = "INSERT INTO recaudaciones (ID_Recaudacion,Tipo_Comercio,ID_Maquina,ID_Usuario,Monto_Total,Monto_Empresa,Monto_Comercio,fecha,detalle,Porcentaje_Comercio)
                 VALUES (?,?,?,?,?,?,?,?,?,?)
                 ON DUPLICATE KEY UPDATE Tipo_Comercio=VALUES(Tipo_Comercio),Monto_Total=VALUES(Monto_Total),
                     Monto_Empresa=VALUES(Monto_Empresa),Monto_Comercio=VALUES(Monto_Comercio),
                     detalle=VALUES(detalle),Porcentaje_Comercio=VALUES(Porcentaje_Comercio)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssssdddsds',
            $data['ID_Recaudacion'],$data['Tipo_Comercio'],$data['ID_Maquina'],$data['ID_Usuario'],
            $data['Monto_Total'],$data['Monto_Empresa'],$data['Monto_Comercio'],
            $data['fecha'],$data['detalle'],$data['Porcentaje_Comercio']);
        $stmt->execute(); $stmt->close();

        $this->cache->delete("recaudacion:id:{$data['ID_Recaudacion']}");
        $this->invalidateListados();
    }

    public function saveInforme(InformeRecaudacion $informe): void
    {
        $conn = $this->db->getConnection();
        $data = $informe->toArray();
        $sql  = "INSERT INTO informes_recaudacion (ID_Informe,ID_Recaudacion,CI_Usuario,Nombre_Maquina,ID_Comercio,Nombre_Comercio,Direccion_Comercio,Telefono_Comercio,Pago_Ensamblador,Pago_Comprobador,Pago_Mantenimiento,empresa_nombre,empresa_descripcion)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)
                 ON DUPLICATE KEY UPDATE CI_Usuario=VALUES(CI_Usuario),Nombre_Maquina=VALUES(Nombre_Maquina),
                     Nombre_Comercio=VALUES(Nombre_Comercio),Direccion_Comercio=VALUES(Direccion_Comercio),
                     Telefono_Comercio=VALUES(Telefono_Comercio),Pago_Ensamblador=VALUES(Pago_Ensamblador),
                     Pago_Comprobador=VALUES(Pago_Comprobador),Pago_Mantenimiento=VALUES(Pago_Mantenimiento)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssssssssddsss',
            $data['ID_Informe'],$data['ID_Recaudacion'],$data['CI_Usuario'],$data['Nombre_Maquina'],
            $data['ID_Comercio'],$data['Nombre_Comercio'],$data['Direccion_Comercio'],$data['Telefono_Comercio'],
            $data['Pago_Ensamblador'],$data['Pago_Comprobador'],$data['Pago_Mantenimiento'],
            $data['empresa_nombre'],$data['empresa_descripcion']);
        $stmt->execute(); $stmt->close();
        $this->cache->delete("recaudacion:informe:{$data['ID_Recaudacion']}");
    }

    public function saveDetalle(DetalleInforme $detalle): void
    {
        $conn = $this->db->getConnection();
        $data = $detalle->toArray();
        $stmt = $conn->prepare("INSERT INTO informe_detalle (ID_Informe_Detalle,ID_Informe,ID_Componente) VALUES (?,?,?)");
        $stmt->bind_param('sss', $data['ID_Informe_Detalle'], $data['ID_Informe'], $data['ID_Componente']);
        $stmt->execute(); $stmt->close();
        $this->cache->delete("recaudacion:detalles:{$data['ID_Informe']}");
    }

    public function delete(Uuid $id): bool
    {
        $conn = $this->db->getConnection();
        try {
            $conn->begin_transaction();
            $v     = $id->value();
            $stmt1 = $conn->prepare("DELETE d FROM informe_detalle d INNER JOIN informes_recaudacion i ON d.ID_Informe=i.ID_Informe WHERE i.ID_Recaudacion=?");
            $stmt1->bind_param('s', $v); $stmt1->execute(); $stmt1->close();
            $stmt2 = $conn->prepare("DELETE FROM informes_recaudacion WHERE ID_Recaudacion=?");
            $stmt2->bind_param('s', $v); $stmt2->execute(); $stmt2->close();
            $stmt3 = $conn->prepare("DELETE FROM recaudaciones WHERE ID_Recaudacion=?");
            $stmt3->bind_param('s', $v); $result = $stmt3->execute(); $stmt3->close();
            $conn->commit();
        } catch (\Exception $e) {
            $conn->rollback();
            error_log("Error al eliminar recaudación: " . $e->getMessage());
            return false;
        }
        $this->cache->delete("recaudacion:id:{$v}");
        $this->cache->delete("recaudacion:informe:{$v}");
        $this->invalidateListados();
        return $result;
    }

    public function findAll(array $filters = [], int $limit = 100, int $offset = 0): array
    {
        $cacheKey = "recaudaciones:all:" . md5(serialize([$filters,$limit,$offset]));
        return $this->cache->remember($cacheKey, function () use ($filters,$limit,$offset) {
            $conn   = $this->db->getConnection();
            $sql    = "SELECT r.*,c.Nombre as Nombre_Comercio,m.Nombre_Maquina,
                              u.nombre as nombre_usuario,u.apellido as apellido_usuario
                       FROM recaudaciones r
                       INNER JOIN MaquinaRecreativa m ON r.ID_Maquina=m.ID_Maquina
                       INNER JOIN Comercio c ON m.ID_Comercio=c.ID_Comercio
                       INNER JOIN usuario u ON r.ID_Usuario=u.ID_Usuario
                       WHERE 1=1";
            $params = []; $types = "";
            if (!empty($filters['fecha_inicio'])) { $sql .= " AND DATE(r.fecha)>=?"; $params[] = $filters['fecha_inicio']; $types .= "s"; }
            if (!empty($filters['fecha_fin']))    { $sql .= " AND DATE(r.fecha)<=?"; $params[] = $filters['fecha_fin'];    $types .= "s"; }
            if (!empty($filters['ID_Maquina']))   { $sql .= " AND r.ID_Maquina=?";  $params[] = $filters['ID_Maquina'];   $types .= "s"; }
            if (!empty($filters['Tipo_Comercio'])) { $sql .= " AND r.Tipo_Comercio=?"; $params[] = $filters['Tipo_Comercio']; $types .= "s"; }
            $sql .= " ORDER BY r.fecha DESC LIMIT ? OFFSET ?";
            $params[] = $limit; $params[] = $offset; $types .= "ii";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $recaudaciones = [];
            while ($row = $stmt->get_result()->fetch_assoc()) $recaudaciones[] = $row;
            $stmt->close();
            return $recaudaciones;
        }, $this->ttl);
    }

    public function findResumenByTipoComercio(?int $limit = null): array
    {
        $cacheKey = "recaudaciones:resumen:" . ($limit ?? 'all');
        return $this->cache->remember($cacheKey, function () use ($limit) {
            $conn = $this->db->getConnection();
            $sql  = "SELECT Tipo_Comercio,COUNT(*) as TotalRecaudaciones,SUM(Monto_Total) as TotalRecaudado,
                            SUM(Monto_Empresa) as TotalEmpresa,SUM(Monto_Comercio) as TotalComercio
                     FROM recaudaciones GROUP BY Tipo_Comercio ORDER BY TotalRecaudado DESC";
            if ($limit !== null) { $sql .= " LIMIT ?"; $stmt = $conn->prepare($sql); $stmt->bind_param('i', $limit); }
            else { $stmt = $conn->prepare($sql); }
            $stmt->execute();
            $resumen = [];
            while ($row = $stmt->get_result()->fetch_assoc()) $resumen[] = $row;
            $stmt->close();
            if (empty($resumen)) $resumen[] = ['Tipo_Comercio'=>'Sin datos','TotalRecaudaciones'=>0,'TotalRecaudado'=>0,'TotalEmpresa'=>0,'TotalComercio'=>0];
            return $resumen;
        }, $this->ttl);
    }

    public function findMaquinasOperativasPorComercio(Comercio $comercio): array
    {
        $cid      = $comercio->getId();
        $cacheKey = "recaudacion:maquinas_comercio:{$cid}";
        return $this->cache->remember($cacheKey, function () use ($cid) {
            $conn = $this->db->getConnection();
            $sql  = "SELECT m.* FROM MaquinaRecreativa m WHERE m.ID_Comercio=? AND m.Estado='Operativa' AND m.Etapa='Recaudacion' ORDER BY m.Nombre_Maquina ASC";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('s', $cid);
            $stmt->execute();
            $maquinas = [];
            while ($row = $stmt->get_result()->fetch_assoc()) $maquinas[] = $row;
            $stmt->close();
            return $maquinas;
        }, 600);
    }

    private function invalidateListados(): void
    {
        $this->cache->delete("recaudaciones:resumen:all");
        $this->cache->delete("recaudacion:maquinas_operativas");
        if ($this->cache instanceof \maquinas_recreativas\Infrastructure\Cache\RedisCache) {
            $this->cache->deleteByPattern("recaudaciones:all:*");
            $this->cache->deleteByPattern("recaudaciones:resumen:*");
            $this->cache->deleteByPattern("recaudacion:maquinas_comercio:*");
        }
    }
}
