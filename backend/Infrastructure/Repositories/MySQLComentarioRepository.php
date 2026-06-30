<?php
/**
 * Infrastructure/Repositories/MySQLComentarioRepository.php
 * Migrado a PDO. TTL: 120s.
 */
namespace maquinas_recreativas\Infrastructure\Repositories;

use PDO;
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

        $checkStmt = $conn->prepare("SELECT 1 FROM comentario WHERE ID_Comentario = ?");
        $checkStmt->execute([$data['ID_Comentario']]);
        $exists = (bool) $checkStmt->fetch(PDO::FETCH_ASSOC);

        if ($exists) {
            $sql = "UPDATE comentario
                    SET comentario = ?, fecha_edicion = ?, eliminado = ?
                    WHERE ID_Comentario = ?";
            $stmt = $conn->prepare($sql);
            $fechaEdicion = date('Y-m-d H:i:s');
            $eliminado = $data['eliminado'] ?? 0;
            $stmt->execute([$data['comentario'], $fechaEdicion, $eliminado, $data['ID_Comentario']]);
        } else {
            $sql = "INSERT INTO comentario (ID_Comentario, ID_Reporte, ID_Usuario_Emisor, comentario, fecha_hora, fecha_edicion, eliminado)
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                $data['ID_Comentario'], $data['ID_Reporte'], $data['ID_Usuario_Emisor'],
                $data['comentario'], $data['fecha_hora'], null, 0
            ]);
        }

        if ($this->cache instanceof \maquinas_recreativas\Infrastructure\Cache\RedisCache) {
            $this->cache->deleteByPattern("comentarios:reporte:{$data['ID_Reporte']}:*");
        }
    }

    public function findById(Uuid $id): ?Comentario
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("SELECT * FROM comentario WHERE ID_Comentario = ?");
        $stmt->execute([$id->value()]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
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
            $stmt->execute([$idUsuario->value(), $idReporte->value()]);
            $comentarios = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if (!empty($row['email'])) {
                    $row['email'] = CifradoHelper::desencriptar($row['email']);
                }
                $comentarios[] = $row;
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
            $stmt->execute([$ev, $dv, $dv, $ev]);
            $comentarios = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if (!empty($row['email'])) {
                    $row['email'] = CifradoHelper::desencriptar($row['email']);
                }
                $comentarios[] = $row;
            }
            return $comentarios;
        }, $this->ttl);
    }

    public function deleteByReporte(Uuid $idReporte): bool
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("DELETE FROM comentario WHERE ID_Reporte = ?");
        $v = $idReporte->value();
        $result = $stmt->execute([$v]);

        if ($this->cache instanceof \maquinas_recreativas\Infrastructure\Cache\RedisCache) {
            $this->cache->deleteByPattern("comentarios:reporte:{$v}:*");
        }
        return $result;
    }

    public function delete(Uuid $id): bool
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("UPDATE comentario SET eliminado = 1 WHERE ID_Comentario = ?");
        $v = $id->value();
        $result = $stmt->execute([$v]);

        $this->cache->delete("comentario:id:{$v}");
        if ($this->cache instanceof \maquinas_recreativas\Infrastructure\Cache\RedisCache) {
            $this->cache->deleteByPattern("comentarios:reporte:*");
            $this->cache->deleteByPattern("comentarios:chat:*");
        }
        return $result;
    }
}