<?php
/**
 * Infrastructure/Repositories/MySQLRecaudacionRepository.php
 * Migrado a PDO. TTL: 300s.
 */
namespace maquinas_recreativas\Infrastructure\Repositories;

use PDO;
use maquinas_recreativas\Domain\Recaudacion\Recaudacion;
use maquinas_recreativas\Domain\Recaudacion\InformeRecaudacion;
use maquinas_recreativas\Domain\Recaudacion\DetalleInforme;
use maquinas_recreativas\Domain\Recaudacion\RecaudacionRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
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
            $stmt->execute([$id->value()]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $data ? Recaudacion::fromArray($data) : null;
        }, $this->ttl);
    }

    public function findInformeByRecaudacion(Uuid $idRecaudacion): ?InformeRecaudacion
    {
        $cacheKey = "recaudacion:informe:{$idRecaudacion->value()}";
        return $this->cache->remember($cacheKey, function () use ($idRecaudacion) {
            $conn = $this->db->getConnection();
            $stmt = $conn->prepare("SELECT * FROM informes_recaudacion WHERE ID_Recaudacion=?");
            $stmt->execute([$idRecaudacion->value()]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
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
            $stmt->execute([$idInforme->value()]);
            $detalles = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) $detalles[] = $row;
            return $detalles;
        }, $this->ttl);
    }

    public function findMaquinasRecaudacion(): array
    {
        $cacheKey = "recaudacion:maquinas_operativas";
        return $this->cache->remember($cacheKey, function () {
            $conn = $this->db->getConnection();
            $sql  = "SELECT
                        m.ID_Maquina, m.Nombre_Maquina, m.Tipo, m.Estado, m.Etapa,
                        m.ID_Comercio, m.ID_Tecnico_Ensamblador, m.ID_Tecnico_Comprobador,
                        m.ID_Tecnico_Mantenimiento, m.Fecha_Registro,
                        c.Nombre as NombreComercio, c.Direccion as DireccionComercio,
                        c.Telefono as TelefonoComercio, c.Tipo as TipoComercio
                     FROM MaquinaRecreativa m
                     LEFT JOIN Comercio c ON m.ID_Comercio = c.ID_Comercio
                     WHERE m.Etapa = 'Recaudacion' AND m.Estado = 'Operativa'
                     ORDER BY m.Fecha_Registro DESC";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $maquinas = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $maquinas[] = $row;
            }
            return $maquinas;
        }, 600);
    }

    public function save(Recaudacion $recaudacion): void
    {
        $conn = $this->db->getConnection();
        $data = $recaudacion->toArray();

        if (!empty($data['fecha'])) {
            $timestamp = strtotime($data['fecha']);
            $data['fecha'] = $timestamp === false ? date('Y-m-d H:i:s') : date('Y-m-d H:i:s', $timestamp);
        } else {
            $data['fecha'] = date('Y-m-d H:i:s');
        }

        $data['detalle'] = (string)($data['detalle'] ?? '');
        $id = $data['ID_Recaudacion'];

        $checkStmt = $conn->prepare("SELECT COUNT(*) as total FROM recaudaciones WHERE ID_Recaudacion = ?");
        $checkStmt->execute([$id]);
        $count = (int) $checkStmt->fetch(PDO::FETCH_ASSOC)['total'];

        if ($count > 0) {
            $sql = "UPDATE recaudaciones SET
                        Tipo_Comercio = ?, ID_Maquina = ?, ID_Usuario = ?,
                        Monto_Total = ?, Monto_Empresa = ?, Monto_Comercio = ?,
                        fecha = ?, detalle = ?, Porcentaje_Comercio = ?
                    WHERE ID_Recaudacion = ?";
            $stmt = $conn->prepare($sql);
            $params = [
                $data['Tipo_Comercio'], $data['ID_Maquina'], $data['ID_Usuario'],
                $data['Monto_Total'], $data['Monto_Empresa'], $data['Monto_Comercio'],
                $data['fecha'], $data['detalle'], $data['Porcentaje_Comercio'], $id
            ];
        } else {
            $sql = "INSERT INTO recaudaciones (ID_Recaudacion,Tipo_Comercio,ID_Maquina,ID_Usuario,Monto_Total,Monto_Empresa,Monto_Comercio,fecha,detalle,Porcentaje_Comercio)
                    VALUES (?,?,?,?,?,?,?,?,?,?)";
            $stmt = $conn->prepare($sql);
            $params = [
                $data['ID_Recaudacion'], $data['Tipo_Comercio'], $data['ID_Maquina'], $data['ID_Usuario'],
                $data['Monto_Total'], $data['Monto_Empresa'], $data['Monto_Comercio'],
                $data['fecha'], $data['detalle'], $data['Porcentaje_Comercio']
            ];
        }

        error_log("SQL: " . $sql);
        error_log("Params: " . json_encode($data));

        if (!$stmt->execute($params)) {
            error_log("Error en execute recaudacion save");
        } else {
            error_log("Filas afectadas: " . $stmt->rowCount());
        }

        $this->cache->delete("recaudacion:id:{$data['ID_Recaudacion']}");
        $this->cache->delete("recaudacion:nombre_maquina:{$data['ID_Maquina']}");
        $this->invalidateListados();
    }

    public function saveInforme(InformeRecaudacion $informe): void
    {
        $conn = $this->db->getConnection();
        $data = $informe->toArray();

        if ($this->db->isPostgres()) {
            $sql  = "INSERT INTO informes_recaudacion (ID_Informe,ID_Recaudacion,CI_Usuario,Nombre_Maquina,ID_Comercio,Nombre_Comercio,Direccion_Comercio,Telefono_Comercio,Pago_Ensamblador,Pago_Comprobador,Pago_Mantenimiento,empresa_nombre,empresa_descripcion)
                     VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)
                     ON CONFLICT (ID_Informe) DO UPDATE SET
                        CI_Usuario=EXCLUDED.CI_Usuario, Nombre_Maquina=EXCLUDED.Nombre_Maquina,
                        Nombre_Comercio=EXCLUDED.Nombre_Comercio, Direccion_Comercio=EXCLUDED.Direccion_Comercio,
                        Telefono_Comercio=EXCLUDED.Telefono_Comercio, Pago_Ensamblador=EXCLUDED.Pago_Ensamblador,
                        Pago_Comprobador=EXCLUDED.Pago_Comprobador, Pago_Mantenimiento=EXCLUDED.Pago_Mantenimiento";
        } else {
            $sql  = "INSERT INTO informes_recaudacion (ID_Informe,ID_Recaudacion,CI_Usuario,Nombre_Maquina,ID_Comercio,Nombre_Comercio,Direccion_Comercio,Telefono_Comercio,Pago_Ensamblador,Pago_Comprobador,Pago_Mantenimiento,empresa_nombre,empresa_descripcion)
                     VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)
                     ON DUPLICATE KEY UPDATE CI_Usuario=VALUES(CI_Usuario),Nombre_Maquina=VALUES(Nombre_Maquina),
                         Nombre_Comercio=VALUES(Nombre_Comercio),Direccion_Comercio=VALUES(Direccion_Comercio),
                         Telefono_Comercio=VALUES(Telefono_Comercio),Pago_Ensamblador=VALUES(Pago_Ensamblador),
                         Pago_Comprobador=VALUES(Pago_Comprobador),Pago_Mantenimiento=VALUES(Pago_Mantenimiento)";
        }
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            $data['ID_Informe'],$data['ID_Recaudacion'],$data['CI_Usuario'],$data['Nombre_Maquina'],
            $data['ID_Comercio'],$data['Nombre_Comercio'],$data['Direccion_Comercio'],$data['Telefono_Comercio'],
            $data['Pago_Ensamblador'],$data['Pago_Comprobador'],$data['Pago_Mantenimiento'],
            $data['empresa_nombre'],$data['empresa_descripcion']
        ]);
        $this->cache->delete("recaudacion:informe:{$data['ID_Recaudacion']}");
    }

    public function saveDetalle(DetalleInforme $detalle): void
    {
        $conn = $this->db->getConnection();
        $data = $detalle->toArray();
        $stmt = $conn->prepare("INSERT INTO informe_detalle (ID_Informe_Detalle,ID_Informe,ID_Componente) VALUES (?,?,?)");
        $stmt->execute([$data['ID_Informe_Detalle'], $data['ID_Informe'], $data['ID_Componente']]);
        $this->cache->delete("recaudacion:detalles:{$data['ID_Informe']}");
    }

    public function delete(Uuid $id): bool
    {
        $conn = $this->db->getConnection();
        try {
            $conn->beginTransaction();
            $v     = $id->value();
            $stmt1 = $conn->prepare("DELETE FROM informe_detalle WHERE ID_Informe IN (SELECT ID_Informe FROM informes_recaudacion WHERE ID_Recaudacion=?)");
            $stmt1->execute([$v]);
            $stmt2 = $conn->prepare("DELETE FROM informes_recaudacion WHERE ID_Recaudacion=?");
            $stmt2->execute([$v]);
            $stmt3 = $conn->prepare("DELETE FROM recaudaciones WHERE ID_Recaudacion=?");
            $result = $stmt3->execute([$v]);
            $conn->commit();
        } catch (\Exception $e) {
            if ($conn->inTransaction()) $conn->rollBack();
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
            $sql = "SELECT
                        r.ID_Recaudacion, r.Tipo_Comercio, r.ID_Maquina, r.ID_Usuario,
                        r.Monto_Total, r.Monto_Empresa, r.Monto_Comercio, r.Porcentaje_Comercio,
                        r.fecha, r.detalle,
                        c.Nombre as Nombre_Comercio, m.Nombre_Maquina,
                        u.nombre as nombre_usuario, u.apellido as apellido_usuario
                    FROM recaudaciones r
                    INNER JOIN MaquinaRecreativa m ON r.ID_Maquina = m.ID_Maquina
                    INNER JOIN Comercio c ON m.ID_Comercio = c.ID_Comercio
                    INNER JOIN usuario u ON r.ID_Usuario = u.ID_Usuario
                    WHERE 1=1";
            $params = [];

            if (!empty($filters['fechaInicio'])) { $sql .= " AND DATE(r.fecha) >= ?"; $params[] = $filters['fechaInicio']; }
            if (!empty($filters['fechaFin'])) { $sql .= " AND DATE(r.fecha) <= ?"; $params[] = $filters['fechaFin']; }
            if (!empty($filters['idMaquina'])) { $sql .= " AND r.ID_Maquina = ?"; $params[] = $filters['idMaquina']; }
            if (!empty($filters['tipoComercio'])) { $sql .= " AND r.Tipo_Comercio = ?"; $params[] = $filters['tipoComercio']; }

            $sql .= " ORDER BY r.fecha DESC LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;

            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            $recaudaciones = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $recaudaciones[] = $row;
            }
            error_log("Recaudaciones encontradas: " . count($recaudaciones));
            return $recaudaciones;
        }, $this->ttl);
    }

    public function findResumenByTipoComercio(?int $limit = null): array
    {
        $cacheKey = "recaudaciones:resumen:" . ($limit ?? 'all');
        return $this->cache->remember($cacheKey, function () use ($limit) {
            $conn = $this->db->getConnection();
            $sql  = "SELECT Tipo_Comercio,
                            COUNT(*) as TotalRecaudaciones,
                            SUM(Monto_Total) as TotalRecaudado,
                            SUM(Monto_Empresa) as TotalEmpresa,
                            SUM(Monto_Comercio) as TotalComercio
                     FROM recaudaciones
                     GROUP BY Tipo_Comercio
                     ORDER BY TotalRecaudado DESC";

            $params = [];
            if ($limit !== null) {
                $sql .= " LIMIT ?";
                $params[] = $limit;
            }
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);

            $resumen = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $resumen[] = $row;
            }

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
        $cid      = $comercio->getId();
        $cacheKey = "recaudacion:maquinas_comercio:{$cid}";
        return $this->cache->remember($cacheKey, function () use ($cid) {
            $conn = $this->db->getConnection();
            $sql  = "SELECT m.* FROM MaquinaRecreativa m
                     WHERE m.ID_Comercio = ?
                       AND m.Estado = 'Operativa'
                       AND m.Etapa = 'Recaudacion'
                     ORDER BY m.Nombre_Maquina ASC";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$cid]);
            $maquinas = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $maquinas[] = $row;
            }
            error_log("findMaquinasOperativasPorComercio: comercio=$cid, máquinas=" . count($maquinas));
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
            return $row ? $row['Nombre_Maquina'] : null;
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