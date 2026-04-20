<?php
/**
 * Infrastructure/Repositories/MySQLComentarioRepository.php
 * TTL: 120 s — chat/tiempo real.
 */
namespace maquinas_recreativas\Infrastructure\Repositories;

use maquinas_recreativas\Domain\Comentario\Comentario;
use maquinas_recreativas\Domain\Comentario\ComentarioRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Infrastructure\Database\Database;
use maquinas_recreativas\Infrastructure\Security\CifradoHelper;
use maquinas_recreativas\Infrastructure\Cache\CacheInterface;
use maquinas_recreativas\Infrastructure\Cache\CacheFactory;

class MySQLComentarioRepository implements ComentarioRepository
{
    private Database       $db;
    private CacheInterface $cache;
    private int            $ttl = 120;

    public function __construct(Database $db, ?CacheInterface $cache = null)
    {
        $this->db    = $db;
        $this->cache = $cache ?? CacheFactory::create();
    }

public function save(Comentario $comentario): void
{
    $conn = $this->db->getConnection();
    $data = $comentario->toArray();
    $sql = "INSERT INTO comentario (ID_Comentario, ID_Reporte, ID_Usuario_Emisor, comentario, fecha_hora) 
            VALUES (?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sssss',
        $data['ID_Comentario'],
        $data['ID_Reporte'],
        $data['ID_Usuario_Emisor'],
        $data['comentario'],
        $data['fecha_hora']
    );
    $stmt->execute();
    $stmt->close();
    
    //  Limpiar resultados pendientes
    while ($conn->more_results() && $conn->next_result()) {
        if ($rs = $conn->store_result()) {
            $rs->free();
        }
    }

    // Invalidar comentarios de ese reporte para todos los usuarios
    if ($this->cache instanceof \maquinas_recreativas\Infrastructure\Cache\RedisCache) {
        $this->cache->deleteByPattern("comentarios:reporte:{$data['ID_Reporte']}:*");
    }
}

public function findById(Uuid $id): ?Comentario
{
    $conn = $this->db->getConnection();
    $stmt = $conn->prepare("SELECT * FROM comentario WHERE ID_Comentario = ?");
    $v = $id->value(); 
    $stmt->bind_param('s', $v);
    $stmt->execute();
    
    //   Obtener el resultado UNA SOLA VEZ
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    
    $result->free();
    $stmt->close();
    
    // Limpiar resultados pendientes
    while ($conn->more_results() && $conn->next_result()) {
        if ($rs = $conn->store_result()) {
            $rs->free();
        }
    }
    
    return $data ? Comentario::fromArray($data) : null;
}
public function findByReporte(Uuid $idReporte, Uuid $idUsuario): array
{
    $cacheKey = "comentarios:reporte:{$idReporte->value()}:{$idUsuario->value()}";
    return $this->cache->remember($cacheKey, function () use ($idReporte, $idUsuario) {
        $conn = $this->db->getConnection();
        $sql  = "SELECT c.*, u.nombre, u.apellido, u.email, u.tipo,
                        CASE WHEN u.ID_Usuario = ? THEN 1 ELSE 0 END as es_propio
                 FROM comentario c 
                 JOIN usuario u ON c.ID_Usuario_Emisor = u.ID_Usuario
                 WHERE c.ID_Reporte = ? 
                 ORDER BY c.fecha_hora ASC";
        
        $stmt = $conn->prepare($sql);
        $uv = $idUsuario->value(); 
        $rv = $idReporte->value();
        $stmt->bind_param('ss', $uv, $rv);
        $stmt->execute();
        
        //   Obtener el resultado UNA SOLA VEZ
        $result = $stmt->get_result();
        $comentarios = [];
        
        while ($row = $result->fetch_assoc()) {
            if (!empty($row['email'])) {
                $row['email'] = CifradoHelper::desencriptar($row['email']);
            }
            $comentarios[] = $row;
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
        
        return $comentarios;
    }, $this->ttl);
}
    public function findByChat(Uuid $emisorId, Uuid $destinatarioId): array
{
    $cacheKey = "comentarios:chat:{$emisorId->value()}:{$destinatarioId->value()}";
    return $this->cache->remember($cacheKey, function () use ($emisorId, $destinatarioId) {
        $conn = $this->db->getConnection();
        $sql  = "SELECT c.*, u.nombre, u.apellido, u.email, u.tipo, 
                        r.ID_Usuario_Destinatario, r.ID_Usuario_Emisor
                 FROM comentario c 
                 JOIN reporte r ON c.ID_Reporte = r.ID_Reporte
                 JOIN usuario u ON c.ID_Usuario_Emisor = u.ID_Usuario
                 WHERE (r.ID_Usuario_Emisor = ? AND r.ID_Usuario_Destinatario = ?)
                    OR (r.ID_Usuario_Emisor = ? AND r.ID_Usuario_Destinatario = ?)
                 ORDER BY c.fecha_hora ASC";
        
        $stmt = $conn->prepare($sql);
        $ev = $emisorId->value(); 
        $dv = $destinatarioId->value();
        $stmt->bind_param('ssss', $ev, $dv, $dv, $ev);
        $stmt->execute();
        
        //   Obtener el resultado UNA SOLA VEZ
        $result = $stmt->get_result();
        $comentarios = [];
        
        while ($row = $result->fetch_assoc()) {
            if (!empty($row['email'])) {
                $row['email'] = CifradoHelper::desencriptar($row['email']);
            }
            $comentarios[] = $row;
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
        
        return $comentarios;
    }, $this->ttl);
}
public function deleteByReporte(Uuid $idReporte): bool
{
    $conn = $this->db->getConnection();
    $stmt = $conn->prepare("DELETE FROM comentario WHERE ID_Reporte = ?");
    $v = $idReporte->value(); 
    $stmt->bind_param('s', $v);
    $result = $stmt->execute();
    $stmt->close();
    
    //  Limpiar resultados pendientes
    while ($conn->more_results() && $conn->next_result()) {
        if ($rs = $conn->store_result()) {
            $rs->free();
        }
    }
    
    if ($this->cache instanceof \maquinas_recreativas\Infrastructure\Cache\RedisCache) {
        $this->cache->deleteByPattern("comentarios:reporte:{$v}:*");
    }
    
    return $result;
}
}
