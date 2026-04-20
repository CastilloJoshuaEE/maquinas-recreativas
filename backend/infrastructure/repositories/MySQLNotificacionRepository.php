<?php
/**
 * Infrastructure/Repositories/MySQLNotificacionRepository.php
 * TTL: 120 s — tiempo real / notificaciones frecuentes.
 */
namespace maquinas_recreativas\Infrastructure\Repositories;

use maquinas_recreativas\Domain\Notificacion\NotificacionMaquina;
use maquinas_recreativas\Domain\Notificacion\NotificacionReporte;
use maquinas_recreativas\Domain\Notificacion\NotificacionRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Infrastructure\Database\Database;
use maquinas_recreativas\Infrastructure\Cache\CacheInterface;
use maquinas_recreativas\Infrastructure\Cache\CacheFactory;

class MySQLNotificacionRepository implements NotificacionRepository
{
    private Database       $db;
    private CacheInterface $cache;
    private int            $ttl = 120;

    public function __construct(Database $db, ?CacheInterface $cache = null)
    {
        $this->db    = $db;
        $this->cache = $cache ?? CacheFactory::create();
    }

public function saveMaquina(NotificacionMaquina $notificacion): void
{
    $conn = $this->db->getConnection();
    $data = $notificacion->toArray();
    $sql  = "INSERT INTO NotificacionMaquinaRecreativa (ID_Notificacion,ID_Remitente,ID_Destinatario,ID_Maquina,Tipo,Mensaje,Fecha,Estado)
             VALUES (?,?,?,?,?,?,?,?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssssssss',
        $data['ID_Notificacion'],$data['ID_Remitente'],$data['ID_Destinatario'],
        $data['ID_Maquina'],$data['Tipo'],$data['Mensaje'],$data['Fecha'],$data['Estado']);
    $stmt->execute();
    $stmt->close();  //  Cerrar statement
    // Limpiar resultados pendientes
    while ($conn->more_results() && $conn->next_result()) {
        if ($rs = $conn->store_result()) {
            $rs->free();
        }
    }
    $this->cache->delete("notificaciones:maquina:{$data['ID_Destinatario']}");
    $this->cache->delete("notificaciones:no_leidas:{$data['ID_Destinatario']}");
}

    public function saveReporte(NotificacionReporte $notificacion): void
    {
        $conn = $this->db->getConnection();
        $data = $notificacion->toArray();
        $sql  = "INSERT INTO notificaciones (ID_Notificaciones,ID_Reporte,ID_Usuario,mensaje,fecha_hora,leida) VALUES (?,?,?,?,?,?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sssssi',
            $data['ID_Notificaciones'],$data['ID_Reporte'],$data['ID_Usuario'],
            $data['mensaje'],$data['fecha_hora'],$data['leida']);
        $stmt->execute(); $stmt->close();
        $this->cache->delete("notificaciones:reporte:{$data['ID_Usuario']}");
        $this->cache->delete("notificaciones:no_leidas:{$data['ID_Usuario']}");
    }

    public function findMaquinaById(Uuid $id): ?NotificacionMaquina
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("SELECT * FROM NotificacionMaquinaRecreativa WHERE ID_Notificacion=?");
        $v    = $id->value(); $stmt->bind_param('s', $v);
        $stmt->execute();
        $data = $stmt->get_result()->fetch_assoc(); $stmt->close();
        return $data ? NotificacionMaquina::fromArray($data) : null;
    }

    public function findReporteById(Uuid $id): ?NotificacionReporte
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("SELECT * FROM notificaciones WHERE ID_Notificaciones=?");
        $v    = $id->value(); $stmt->bind_param('s', $v);
        $stmt->execute();
        $data = $stmt->get_result()->fetch_assoc(); $stmt->close();
        return $data ? NotificacionReporte::fromArray($data) : null;
    }
public function findMaquinasByDestinatario(Uuid $idDestinatario): array
{
    $cacheKey = "notificaciones:maquina:{$idDestinatario->value()}";
    return $this->cache->remember($cacheKey, function () use ($idDestinatario) {
        $conn = $this->db->getConnection();
        $sql  = "SELECT n.*, u.nombre as nombre_remitente, u.apellido as apellido_remitente,
                        m.Nombre_Maquina, c.Nombre as NombreComercio, c.Direccion as DireccionComercio
                 FROM NotificacionMaquinaRecreativa n
                 LEFT JOIN usuario u ON n.ID_Remitente = u.ID_Usuario
                 LEFT JOIN MaquinaRecreativa m ON n.ID_Maquina = m.ID_Maquina
                 LEFT JOIN Comercio c ON m.ID_Comercio = c.ID_Comercio
                 WHERE n.ID_Destinatario = ? 
                 ORDER BY n.Fecha DESC";
        
        $stmt = $conn->prepare($sql);
        $v = $idDestinatario->value(); 
        $stmt->bind_param('s', $v);
        $stmt->execute();
        
        //  Obtener resultado una sola vez
        $result = $stmt->get_result();
        $notificaciones = [];
        
        while ($row = $result->fetch_assoc()) {
            $notificaciones[] = $row;
        }
        
        //  Liberar recursos
        $result->free();
        $stmt->close();
        
        // Limpiar resultados pendientes
        while ($conn->more_results() && $conn->next_result()) {
            if ($rs = $conn->store_result()) {
                $rs->free();
            }
        }
        
        return $notificaciones;
    }, $this->ttl);
}

    public function findReportesByUsuario(Uuid $idUsuario): array
{
    $cacheKey = "notificaciones:reporte:{$idUsuario->value()}";
    return $this->cache->remember($cacheKey, function () use ($idUsuario) {
        $conn = $this->db->getConnection();
        $sql  = "SELECT n.*, r.descripcion as reporte_descripcion 
                 FROM notificaciones n
                 LEFT JOIN reporte r ON n.ID_Reporte = r.ID_Reporte
                 WHERE n.ID_Usuario = ? 
                 ORDER BY n.fecha_hora DESC";
        
        $stmt = $conn->prepare($sql);
        $v = $idUsuario->value(); 
        $stmt->bind_param('s', $v);
        $stmt->execute();
        
        //   Obtener el resultado UNA SOLA VEZ
        $result = $stmt->get_result();
        $notificaciones = [];
        
        while ($row = $result->fetch_assoc()) {
            $notificaciones[] = $row;
        }
        
        //  Liberar recursos correctamente
        $result->free();
        $stmt->close();
        
        // Limpiar resultados pendientes
        while ($conn->more_results() && $conn->next_result()) {
            if ($rs = $conn->store_result()) {
                $rs->free();
            }
        }
        
        return $notificaciones;
    }, $this->ttl);
}
public function findNoLeidasMaquina(Uuid $idDestinatario): int
{
    $cacheKey = "notificaciones:no_leidas_maquina:{$idDestinatario->value()}";
    return $this->cache->remember($cacheKey, function () use ($idDestinatario) {
        $conn = $this->db->getConnection();
        $sql = "SELECT COUNT(*) as total 
                FROM NotificacionMaquinaRecreativa 
                WHERE ID_Destinatario = ? AND Estado = 'No leido'";
        
        $stmt = $conn->prepare($sql);
        $v = $idDestinatario->value(); 
        $stmt->bind_param('s', $v);
        $stmt->execute();
        
        //  Obtener resultado una sola vez
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        $result->free();
        $stmt->close();
        
        // Limpiar resultados pendientes
        while ($conn->more_results() && $conn->next_result()) {
            if ($rs = $conn->store_result()) {
                $rs->free();
            }
        }
        
        return (int)($row['total'] ?? 0);
    }, $this->ttl);
}
public function findNoLeidasReporte(Uuid $idUsuario): int
{
    $cacheKey = "notificaciones:no_leidas_reporte:{$idUsuario->value()}";
    return $this->cache->remember($cacheKey, function () use ($idUsuario) {
        $conn = $this->db->getConnection();
        $sql = "SELECT COUNT(*) as cantidad 
                FROM notificaciones 
                WHERE ID_Usuario = ? AND leida = 0";
        
        $stmt = $conn->prepare($sql);
        $v = $idUsuario->value(); 
        $stmt->bind_param('s', $v);
        $stmt->execute();
        
        //  Obtener resultado una sola vez
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        $result->free();
        $stmt->close();
        
        // Limpiar resultados pendientes
        while ($conn->more_results() && $conn->next_result()) {
            if ($rs = $conn->store_result()) {
                $rs->free();
            }
        }
        
        return (int)($row['cantidad'] ?? 0);
    }, $this->ttl);
}
    public function marcarLeidaMaquina(Uuid $id): bool
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("UPDATE NotificacionMaquinaRecreativa SET Estado='Leido' WHERE ID_Notificacion=?");
        $v    = $id->value(); $stmt->bind_param('s', $v);
        $result = $stmt->execute(); $stmt->close();
        // Invalidar no-leídas (no sabemos el destinatario directamente, así que borramos por patrón)
        if ($this->cache instanceof \maquinas_recreativas\Infrastructure\Cache\RedisCache) {
            $this->cache->deleteByPattern("notificaciones:maquina:*");
            $this->cache->deleteByPattern("notificaciones:no_leidas*");
        }
        return $result;
    }

public function marcarLeidaReporte(Uuid $id, Uuid $idUsuario): bool
{
    $conn = $this->db->getConnection();
    $idV = $id->value(); 
    $uV = $idUsuario->value();
    
    // Verificar si existe y no está leída
    $check = $conn->prepare("SELECT ID_Notificaciones, leida FROM notificaciones WHERE ID_Notificaciones = ? AND ID_Usuario = ?");
    $check->bind_param('ss', $idV, $uV);
    $check->execute();
    
    //  Obtener resultado una sola vez
    $result = $check->get_result();
    
    if ($result->num_rows === 0) { 
        $result->free();
        $check->close(); 
        return false; 
    }
    
    $row = $result->fetch_assoc();
    $result->free();
    $check->close();
    
    if ($row['leida'] == 1) {
        return true;
    }
    
    // Actualizar a leída
    $stmt = $conn->prepare("UPDATE notificaciones SET leida = 1 WHERE ID_Notificaciones = ? AND ID_Usuario = ?");
    $stmt->bind_param('ss', $idV, $uV);
    $res = $stmt->execute(); 
    $stmt->close();
    
    // Limpiar resultados pendientes
    while ($conn->more_results() && $conn->next_result()) {
        if ($rs = $conn->store_result()) {
            $rs->free();
        }
    }
    
    // Invalidar caché
    $this->cache->delete("notificaciones:reporte:{$uV}");
    $this->cache->delete("notificaciones:no_leidas:{$uV}");
    $this->cache->delete("notificaciones:no_leidas_reporte:{$uV}");
    
    return $res;
}

    public function marcarTodasLeidasReporte(Uuid $idUsuario): bool
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("UPDATE notificaciones SET leida=1 WHERE ID_Usuario=?");
        $v    = $idUsuario->value(); $stmt->bind_param('s', $v);
        $res  = $stmt->execute(); $stmt->close();
        $this->cache->delete("notificaciones:reporte:{$v}");
        $this->cache->delete("notificaciones:no_leidas:{$v}");
        $this->cache->delete("notificaciones:no_leidas_reporte:{$v}");
        return $res;
    }

}
