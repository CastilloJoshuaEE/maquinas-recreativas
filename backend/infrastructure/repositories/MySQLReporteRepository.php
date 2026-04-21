<?php
/**
 * Infrastructure/Repositories/MySQLReporteRepository.php
 * TTL: 120 s — chat/tiempo real.
 */
namespace maquinas_recreativas\Infrastructure\Repositories;

use maquinas_recreativas\Domain\Reporte\Reporte;
use maquinas_recreativas\Domain\Reporte\EstadoReporte;
use maquinas_recreativas\Domain\Reporte\ReporteRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Infrastructure\Database\Database;
use maquinas_recreativas\Infrastructure\Security\CifradoHelper;
use maquinas_recreativas\Infrastructure\Cache\CacheInterface;
use maquinas_recreativas\Infrastructure\Cache\CacheFactory;

class MySQLReporteRepository implements ReporteRepository
{
    private Database       $db;
    private CacheInterface $cache;
    private int            $ttl = 120;

    public function __construct(Database $db, ?CacheInterface $cache = null)
    {
        $this->db    = $db;
        $this->cache = $cache ?? CacheFactory::create();
    }
public function save(Reporte $reporte): void
{
    $conn = $this->db->getConnection();
    $data = $reporte->toArray();
    
    // Asegurar que los valores no sean null para bind_param
    $idReporte = $data['ID_Reporte'];
    $idEmisor = $data['ID_Usuario_Emisor'];
    $idDestinatario = $data['ID_Usuario_Destinatario'] ?? '';
    $descripcion = $data['descripcion'];
    $fechaHora = $data['fecha_hora'];
    $estado = $data['estado'];
      if (empty($idEmisor)) {
        error_log("Error: ID_Usuario_Emisor es nulo o vacío para reporte $idReporte");
        throw new \Exception('El emisor del reporte no puede ser nulo');
    }
    $sql = "INSERT INTO reporte (ID_Reporte,ID_Usuario_Emisor,ID_Usuario_Destinatario,descripcion,fecha_hora,estado)
            VALUES (?,?,?,?,?,?)
            ON DUPLICATE KEY UPDATE estado=VALUES(estado),descripcion=VALUES(descripcion)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssssss', $idReporte, $idEmisor, $idDestinatario, $descripcion, $fechaHora, $estado);
        $result = $stmt->execute();
        if (!$result) {
        error_log("Error en insert de reporte: " . $stmt->error);
        throw new \Exception('Error al guardar el reporte en la base de datos');
    }
    $stmt->close();

    $this->cache->delete("reporte:id:{$idReporte}");
    $this->cache->delete("reportes:usuario:{$idEmisor}");
    
    // Solo invalidar chat si hay destinatario
    if (!empty($idDestinatario)) {
        $this->cache->delete("reportes:usuario:{$idDestinatario}");
        $this->invalidateChat($idEmisor, $idDestinatario);
    } else {
        if ($this->cache instanceof \maquinas_recreativas\Infrastructure\Cache\RedisCache) {
            $this->cache->deleteByPattern("reportes:usuario:*");
        }
    }
}
private function invalidateChat(string $uid1, string $uid2): void
{
    // Asegurar que ambos IDs no sean null
    if ($uid1 === null || $uid2 === null) {
        return;
    }
    $this->cache->delete("reportes:chat:{$uid1}:{$uid2}");
    $this->cache->delete("reportes:chat:{$uid2}:{$uid1}");
    $this->cache->delete("reportes:usuarios_chat:{$uid1}");
    $this->cache->delete("reportes:usuarios_chat:{$uid2}");
}

    public function findById(Uuid $id): ?Reporte
{
    $cacheKey = "reporte:id:{$id->value()}";
    return $this->cache->remember($cacheKey, function () use ($id) {
        $conn = $this->db->getConnection();
        $sql  = "SELECT r.*, e.nombre as emisor_nombre, e.apellido as emisor_apellido, e.email as emisor_email,
                        d.nombre as destinatario_nombre, d.apellido as destinatario_apellido, d.email as destinatario_email
                 FROM reporte r 
                 JOIN usuario e ON r.ID_Usuario_Emisor = e.ID_Usuario
                 LEFT JOIN usuario d ON r.ID_Usuario_Destinatario = d.ID_Usuario 
                 WHERE r.ID_Reporte = ?";
        
        $stmt = $conn->prepare($sql);
        $v = $id->value(); 
        $stmt->bind_param('s', $v);
        $stmt->execute();
        
        //   Obtener el resultado UNA SOLA VEZ
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        
        $result->free();
        $stmt->close();
        
        if (!$data) {
            return null;
        }
        
        if (!empty($data['emisor_email'])) {
            $data['emisor_email'] = CifradoHelper::desencriptar($data['emisor_email']);
        }
        if (!empty($data['destinatario_email'])) {
            $data['destinatario_email'] = CifradoHelper::desencriptar($data['destinatario_email']);
        }
        
        return Reporte::fromArray($data);
    }, $this->ttl);
}

public function findByUsuario(Uuid $idUsuario): array
{
    $cacheKey = "reportes:usuario:{$idUsuario->value()}";
    return $this->cache->remember($cacheKey, function () use ($idUsuario) {
        $conn = $this->db->getConnection();
        $sql  = "SELECT r.*, e.nombre as emisor_nombre, e.apellido as emisor_apellido, e.email as emisor_email,
                        d.nombre as destinatario_nombre, d.apellido as destinatario_apellido, d.email as destinatario_email
                 FROM reporte r 
                 JOIN usuario e ON r.ID_Usuario_Emisor = e.ID_Usuario
                 LEFT JOIN usuario d ON r.ID_Usuario_Destinatario = d.ID_Usuario
                 WHERE r.ID_Usuario_Emisor = ? OR r.ID_Usuario_Destinatario = ? 
                 ORDER BY r.fecha_hora DESC";
        
        $stmt = $conn->prepare($sql);
        $v = $idUsuario->value(); 
        $stmt->bind_param('ss', $v, $v);
        $stmt->execute();
        
        //   Obtener el resultado UNA SOLA VEZ
        $result = $stmt->get_result();
        $reportes = [];
        
        while ($row = $result->fetch_assoc()) {
            if (!empty($row['emisor_email'])) {
                $row['emisor_email'] = CifradoHelper::desencriptar($row['emisor_email']);
            }
            if (!empty($row['destinatario_email'])) {
                $row['destinatario_email'] = CifradoHelper::desencriptar($row['destinatario_email']);
            }
            $reportes[] = Reporte::fromArray($row);
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
        
        return $reportes;
    }, $this->ttl);
}

    public function findChat(Uuid $emisorId, Uuid $destinatarioId): array
{
    $cacheKey = "reportes:chat:{$emisorId->value()}:{$destinatarioId->value()}";
    return $this->cache->remember($cacheKey, function () use ($emisorId, $destinatarioId) {
        $conn = $this->db->getConnection();
        $sql  = "SELECT r.*, e.nombre as emisor_nombre, e.apellido as emisor_apellido, e.email as emisor_email,
                        d.nombre as destinatario_nombre, d.apellido as destinatario_apellido, d.email as destinatario_email
                 FROM reporte r 
                 JOIN usuario e ON r.ID_Usuario_Emisor = e.ID_Usuario
                 LEFT JOIN usuario d ON r.ID_Usuario_Destinatario = d.ID_Usuario
                 WHERE (r.ID_Usuario_Emisor = ? AND r.ID_Usuario_Destinatario = ?)
                    OR (r.ID_Usuario_Emisor = ? AND r.ID_Usuario_Destinatario = ?)
                 ORDER BY r.fecha_hora ASC";
        
        $stmt = $conn->prepare($sql);
        $ev = $emisorId->value(); 
        $dv = $destinatarioId->value();
        $stmt->bind_param('ssss', $ev, $dv, $dv, $ev);
        $stmt->execute();
        
        //   Obtener el resultado UNA SOLA VEZ
        $result = $stmt->get_result();
        $reportes = [];
        
        while ($row = $result->fetch_assoc()) {
            if (!empty($row['emisor_email'])) {
                $row['emisor_email'] = CifradoHelper::desencriptar($row['emisor_email']);
            }
            if (!empty($row['destinatario_email'])) {
                $row['destinatario_email'] = CifradoHelper::desencriptar($row['destinatario_email']);
            }
            $reportes[] = Reporte::fromArray($row);
        }
        
        $result->free();
        $stmt->close();
        
        return $reportes;
    }, $this->ttl);
}

    public function findUsuariosChat(Uuid $idUsuario): array
{
    $cacheKey = "reportes:usuarios_chat:{$idUsuario->value()}";
    return $this->cache->remember($cacheKey, function () use ($idUsuario) {
        $conn = $this->db->getConnection();
        $sql  = "SELECT DISTINCT u.* FROM usuario u
                 WHERE u.ID_Usuario IN (
                     SELECT DISTINCT ID_Usuario_Emisor FROM reporte WHERE ID_Usuario_Destinatario = ?
                     UNION
                     SELECT DISTINCT ID_Usuario_Destinatario FROM reporte WHERE ID_Usuario_Emisor = ?
                 ) AND u.ID_Usuario != ? 
                 ORDER BY u.nombre ASC";
        
        $stmt = $conn->prepare($sql);
        $v = $idUsuario->value(); 
        $stmt->bind_param('sss', $v, $v, $v);
        $stmt->execute();
        
        //   Obtener el resultado UNA SOLA VEZ
        $result = $stmt->get_result();
        $usuarios = [];
        
        while ($row = $result->fetch_assoc()) {
            if (!empty($row['email'])) {
                $row['email'] = CifradoHelper::desencriptar($row['email']);
            }
            $usuarios[] = $row;
        }
        
        $result->free();
        $stmt->close();
        
        return $usuarios;
    }, $this->ttl);
}

    public function updateEstado(Uuid $id, EstadoReporte $estado): bool
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("UPDATE reporte SET estado=? WHERE ID_Reporte=?");
        $sv   = $estado->value(); $iv = $id->value();
        $stmt->bind_param('ss', $sv, $iv);
        $result = $stmt->execute(); $stmt->close();
        $this->cache->delete("reporte:id:{$iv}");
        // Invalidar listados de usuarios involucrados (patrón)
        if ($this->cache instanceof \maquinas_recreativas\Infrastructure\Cache\RedisCache) {
            $this->cache->deleteByPattern("reportes:usuario:*");
        }
        return $result;
    }


}
