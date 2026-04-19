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
                 FROM MaquinaRecreativa m 
                 LEFT JOIN Comercio c ON m.ID_Comercio = c.ID_Comercio
                 WHERE m.Etapa = 'Recaudacion' AND m.Estado = 'Operativa' 
                 ORDER BY m.Fecha_Registro DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $maquinas = [];
        while ($row = $result->fetch_assoc()) {
            $maquinas[] = $row;
        }
        $result->free();  //  Liberar resultado
        $stmt->close();   //  Cerrar statement
        // Limpiar resultados pendientes
        while ($conn->more_results() && $conn->next_result()) {
            if ($rs = $conn->store_result()) {
                $rs->free();
            }
        }
        return $maquinas;
    }, 600);
}
public function save(Recaudacion $recaudacion): void
{
    $conn = $this->db->getConnection();
    $data = $recaudacion->toArray();

    // ✅ Asegurar que la fecha tenga formato MySQL válido
    if (!empty($data['fecha'])) {
        $timestamp = strtotime($data['fecha']);
        if ($timestamp === false) {
            error_log("Fecha inválida recibida: " . $data['fecha']);
            $data['fecha'] = date('Y-m-d H:i:s'); // fallback a ahora
        } else {
            $data['fecha'] = date('Y-m-d H:i:s', $timestamp);
        }
    } else {
        $data['fecha'] = date('Y-m-d H:i:s');
    }

    $id = $data['ID_Recaudacion'];
    $checkStmt = $conn->prepare("SELECT COUNT(*) FROM recaudaciones WHERE ID_Recaudacion = ?");
    $checkStmt->bind_param('s', $id);
    $checkStmt->execute();
    $checkStmt->bind_result($count);
    $checkStmt->fetch();
    $checkStmt->close();
if ($count > 0) {
    // UPDATE explícito con tipos correctos
    $sql = "UPDATE recaudaciones SET 
                Tipo_Comercio = ?,
                ID_Maquina = ?,
                ID_Usuario = ?,
                Monto_Total = ?,
                Monto_Empresa = ?,
                Monto_Comercio = ?,
                fecha = ?,
                detalle = ?,
                Porcentaje_Comercio = ?
            WHERE ID_Recaudacion = ?";
    $stmt = $conn->prepare($sql);
    // Cadena de tipos: sss (3 strings) + ddd (3 doubles) + ss (2 strings) + d (1 double) + s (1 string) = 10
    $stmt->bind_param(
        'sssdddssds',
        $data['Tipo_Comercio'],
        $data['ID_Maquina'],
        $data['ID_Usuario'],
        $data['Monto_Total'],
        $data['Monto_Empresa'],
        $data['Monto_Comercio'],
        $data['fecha'],
        $data['detalle'],
        $data['Porcentaje_Comercio'],
        $id
    );
}else {
        // INSERT
        $sql = "INSERT INTO recaudaciones (ID_Recaudacion,Tipo_Comercio,ID_Maquina,ID_Usuario,Monto_Total,Monto_Empresa,Monto_Comercio,fecha,detalle,Porcentaje_Comercio)
                VALUES (?,?,?,?,?,?,?,?,?,?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssssdddsds',
            $data['ID_Recaudacion'],$data['Tipo_Comercio'],$data['ID_Maquina'],$data['ID_Usuario'],
            $data['Monto_Total'],$data['Monto_Empresa'],$data['Monto_Comercio'],
            $data['fecha'],$data['detalle'],$data['Porcentaje_Comercio']
        );
    }
    
    error_log("SQL: " . $sql);
    error_log("Params: " . json_encode($data));
    
    if (!$stmt->execute()) {
        error_log("Error en execute: " . $stmt->error);
    }
    $stmt->close();
    
    // Invalidar caché
    $this->cache->delete("recaudacion:id:{$data['ID_Recaudacion']}");
    $this->cache->delete("recaudacion:nombre_maquina:{$data['ID_Maquina']}");
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
                   INNER JOIN MaquinaRecreativa m ON r.ID_Maquina = m.ID_Maquina
                   INNER JOIN Comercio c ON m.ID_Comercio = c.ID_Comercio
                   INNER JOIN usuario u ON r.ID_Usuario = u.ID_Usuario
                   WHERE 1=1";
        $params = []; 
        $types = "";
        
        //  Usar los nombres correctos de los filtros
        if (!empty($filters['fechaInicio'])) { 
            $sql .= " AND DATE(r.fecha) >= ?"; 
            $params[] = $filters['fechaInicio']; 
            $types .= "s"; 
        }
        if (!empty($filters['fechaFin'])) {    
            $sql .= " AND DATE(r.fecha) <= ?"; 
            $params[] = $filters['fechaFin'];    
            $types .= "s"; 
        }
        if (!empty($filters['idMaquina'])) {   
            $sql .= " AND r.ID_Maquina = ?";  
            $params[] = $filters['idMaquina'];   
            $types .= "s"; 
        }
        if (!empty($filters['tipoComercio'])) { 
            $sql .= " AND r.Tipo_Comercio = ?"; 
            $params[] = $filters['tipoComercio']; 
            $types .= "s"; 
        }
        
        $sql .= " ORDER BY r.fecha DESC LIMIT ? OFFSET ?";
        $params[] = $limit; 
        $params[] = $offset; 
        $types .= "ii";
        
        error_log("SQL Recaudaciones: " . $sql);
        error_log("Params: " . json_encode($params));
        
        $stmt = $conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $recaudaciones = [];
        while ($row = $result->fetch_assoc()) {
            $recaudaciones[] = $row;
        }
        $result->free();
        $stmt->close();
        
        while ($conn->more_results() && $conn->next_result()) {
            if ($rs = $conn->store_result()) {
                $rs->free();
            }
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
        
        if ($limit !== null) { 
            $sql .= " LIMIT ?"; 
            $stmt = $conn->prepare($sql); 
            $stmt->bind_param('i', $limit); 
        } else { 
            $stmt = $conn->prepare($sql); 
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $resumen = [];
        while ($row = $result->fetch_assoc()) {
            $resumen[] = $row;
        }
        $result->free();  //  Liberar resultado
        $stmt->close();   //  Cerrar statement
        // Limpiar resultados pendientes
        while ($conn->more_results() && $conn->next_result()) {
            if ($rs = $conn->store_result()) {
                $rs->free();
            }
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
        $stmt->bind_param('s', $cid);
        $stmt->execute();
        $result = $stmt->get_result();
        $maquinas = [];
        while ($row = $result->fetch_assoc()) {
            $maquinas[] = $row;  //  array asociativo
        }
        $result->free();
        $stmt->close();
        $this->db->clearPendingResults($conn);
        
        error_log("findMaquinasOperativasPorComercio: comercio=$cid, máquinas=" . count($maquinas));
        return $maquinas;
    }, 600);
}
/**
 * Obtiene el nombre de una máquina por su ID
 * @param Uuid $idMaquina
 * @return string|null
 */
public function findNombreMaquinaById(Uuid $idMaquina): ?string
{
    $cacheKey = "recaudacion:nombre_maquina:{$idMaquina->value()}";
    return $this->cache->remember($cacheKey, function () use ($idMaquina) {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("SELECT Nombre_Maquina FROM MaquinaRecreativa WHERE ID_Maquina = ?");
        $v = $idMaquina->value();
        $stmt->bind_param('s', $v);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row ? $row['Nombre_Maquina'] : null;
    }, $this->ttl);
}
private function invalidateListados(): void
{
    // Eliminar claves específicas
    $this->cache->delete("recaudaciones:resumen:all");
    $this->cache->delete("recaudacion:maquinas_operativas");
    
    // Si es Redis, eliminar por patrón
    if ($this->cache instanceof \maquinas_recreativas\Infrastructure\Cache\RedisCache) {
        $this->cache->deleteByPattern("recaudaciones:all:*");
        $this->cache->deleteByPattern("recaudaciones:resumen:*");
        $this->cache->deleteByPattern("recaudacion:maquinas_comercio:*");
        $this->cache->deleteByPattern("recaudacion:nombre_maquina:*");
    } else {
        // Para NullCache (sin Redis), no hay problema
    }
}
}
