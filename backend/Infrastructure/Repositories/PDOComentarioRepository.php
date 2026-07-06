<?php
/**
 * Infrastructure/Repositories/PDOComentarioRepository.php
 */
namespace maquinas_recreativas\Infrastructure\Repositories;

use maquinas_recreativas\Domain\Comentario\Comentario;
use maquinas_recreativas\Domain\Comentario\ComentarioRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Infrastructure\Database\Database;
use maquinas_recreativas\Infrastructure\Security\CifradoHelper;
use maquinas_recreativas\Infrastructure\Cache\CacheInterface;
use maquinas_recreativas\Infrastructure\Cache\CacheFactory;
use PDO;

class PDOComentarioRepository implements ComentarioRepository
{
    private Database $db;
    private CacheInterface $cache;
    private int $ttl = 120;

    public function __construct(Database $db, ?CacheInterface $cache = null)
    {
        $this->db = $db;
        $this->cache = $cache ?? CacheFactory::create();
    }

    public function save(Comentario $comentario): void
    {
        $conn = $this->db->getConnection();
        $data = $comentario->toArray();

        $existing = $this->findById($comentario->id());

        if ($existing) {
            $stmt = $conn->prepare("CALL sp_editar_comentario(?, ?, ?)");
            $fechaEdicion = date('Y-m-d H:i:s');
            $stmt->execute([$data['ID_Comentario'], $data['comentario'], $fechaEdicion]);
        } else {
            $stmt = $conn->prepare("CALL sp_insertar_comentario(?, ?, ?, ?, ?)");
            $stmt->execute([
                $data['ID_Comentario'],
                $data['ID_Reporte'],
                $data['ID_Usuario_Emisor'],
                $data['comentario'],
                $data['fecha_hora']
            ]);
        }
        $stmt->closeCursor();
        $this->db->clearPendingResults();

        if ($this->cache instanceof \maquinas_recreativas\Infrastructure\Cache\RedisCache) {
            $this->cache->deleteByPattern("comentarios:reporte:{$data['ID_Reporte']}:*");
        }
    }

    public function findById(Uuid $id): ?Comentario
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("CALL sp_buscar_comentario_por_id(?)");
        $stmt->execute([$id->value()]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        $this->db->clearPendingResults();
        
        return $data ? Comentario::fromArray($data) : null;
    }

    public function findByReporte(Uuid $idReporte, Uuid $idUsuario): array
    {
        $cacheKey = "comentarios:reporte:{$idReporte->value()}:{$idUsuario->value()}";
        return $this->cache->remember($cacheKey, function () use ($idReporte, $idUsuario) {
            $conn = $this->db->getConnection();
            $stmt = $conn->prepare("CALL sp_comentarios_por_reporte(?, ?)");
            $stmt->execute([$idReporte->value(), $idUsuario->value()]);
            
            $comentarios = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if (!empty($row['email'])) {
                    $row['email'] = CifradoHelper::desencriptar($row['email']);
                }
                $comentarios[] = $row;
            }
            $stmt->closeCursor();
            $this->db->clearPendingResults();
            
            return $comentarios;
        }, $this->ttl);
    }

    public function findByChat(Uuid $emisorId, Uuid $destinatarioId): array
    {
        $cacheKey = "comentarios:chat:{$emisorId->value()}:{$destinatarioId->value()}";
        return $this->cache->remember($cacheKey, function () use ($emisorId, $destinatarioId) {
            $conn = $this->db->getConnection();
            $sql = "SELECT c.*, u.nombre, u.apellido, u.email, u.tipo, 
                           r.ID_Usuario_Destinatario, r.ID_Usuario_Emisor
                    FROM comentario c 
                    JOIN reporte r ON c.ID_Reporte = r.ID_Reporte
                    JOIN usuario u ON c.ID_Usuario_Emisor = u.ID_Usuario
                    WHERE (r.ID_Usuario_Emisor = ? AND r.ID_Usuario_Destinatario = ?)
                       OR (r.ID_Usuario_Emisor = ? AND r.ID_Usuario_Destinatario = ?)
                    ORDER BY c.fecha_hora ASC";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([$emisorId->value(), $destinatarioId->value(), $destinatarioId->value(), $emisorId->value()]);
            
            $comentarios = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if (!empty($row['email'])) {
                    $row['email'] = CifradoHelper::desencriptar($row['email']);
                }
                $comentarios[] = $row;
            }
            $stmt->closeCursor();
            $this->db->clearPendingResults();
            
            return $comentarios;
        }, $this->ttl);
    }

    public function deleteByReporte(Uuid $idReporte): bool
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("CALL sp_eliminar_comentarios_reporte(?)");
        $result = $stmt->execute([$idReporte->value()]);
        $stmt->closeCursor();
        $this->db->clearPendingResults();

        if ($this->cache instanceof \maquinas_recreativas\Infrastructure\Cache\RedisCache) {
            $this->cache->deleteByPattern("comentarios:reporte:{$idReporte->value()}:*");
        }

        return $result;
    }

    public function delete(Uuid $id): bool
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("CALL sp_eliminar_comentario(?)");
        $result = $stmt->execute([$id->value()]);
        $stmt->closeCursor();
        $this->db->clearPendingResults();

        $this->cache->delete("comentario:id:{$id->value()}");
        if ($this->cache instanceof \maquinas_recreativas\Infrastructure\Cache\RedisCache) {
            $this->cache->deleteByPattern("comentarios:reporte:*");
            $this->cache->deleteByPattern("comentarios:chat:*");
        }

        return $result;
    }
}