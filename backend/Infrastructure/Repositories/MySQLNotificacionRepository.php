<?php
/**
 * Infrastructure/Repositories/MySQLNotificacionRepository.php
 */
namespace maquinas_recreativas\Infrastructure\Repositories;

use maquinas_recreativas\Domain\Notificacion\NotificacionMaquina;
use maquinas_recreativas\Domain\Notificacion\NotificacionReporte;
use maquinas_recreativas\Domain\Notificacion\NotificacionRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Infrastructure\Database\Database;
use maquinas_recreativas\Infrastructure\Cache\CacheInterface;
use maquinas_recreativas\Infrastructure\Cache\CacheFactory;
use PDO;

class MySQLNotificacionRepository implements NotificacionRepository
{
    private Database $db;
    private CacheInterface $cache;
    private int $ttl = 120;

    public function __construct(Database $db, ?CacheInterface $cache = null)
    {
        $this->db = $db;
        $this->cache = $cache ?? CacheFactory::create();
    }

    public function saveMaquina(NotificacionMaquina $notificacion): void
    {
        $conn = $this->db->getConnection();
        $data = $notificacion->toArray();

        $stmt = $conn->prepare("CALL sp_crear_notificacion_maquina(?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['ID_Notificacion'],
            $data['ID_Remitente'],
            $data['ID_Destinatario'],
            $data['ID_Maquina'],
            $data['Tipo'],
            $data['Mensaje'],
            $data['Fecha']
        ]);
        $stmt->closeCursor();
        $this->db->clearPendingResults();

        $this->cache->delete("notificaciones:maquina:{$data['ID_Destinatario']}");
        $this->cache->delete("notificaciones:no_leidas:{$data['ID_Destinatario']}");
    }

    public function saveReporte(NotificacionReporte $notificacion): void
    {
        $conn = $this->db->getConnection();
        $data = $notificacion->toArray();

        $stmt = $conn->prepare("CALL sp_crear_notificacion_reporte(?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['ID_Notificaciones'],
            $data['ID_Reporte'],
            $data['ID_Usuario'],
            $data['mensaje'],
            $data['fecha_hora']
        ]);
        $stmt->closeCursor();
        $this->db->clearPendingResults();

        $this->cache->delete("notificaciones:reporte:{$data['ID_Usuario']}");
        $this->cache->delete("notificaciones:no_leidas:{$data['ID_Usuario']}");
    }

    public function findMaquinaById(Uuid $id): ?NotificacionMaquina
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("SELECT * FROM NotificacionMaquinaRecreativa WHERE ID_Notificacion = ?");
        $stmt->execute([$id->value()]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        $this->db->clearPendingResults();
        
        return $data ? NotificacionMaquina::fromArray($data) : null;
    }

    public function findReporteById(Uuid $id): ?NotificacionReporte
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("SELECT * FROM notificaciones WHERE ID_Notificaciones = ?");
        $stmt->execute([$id->value()]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        $this->db->clearPendingResults();
        
        return $data ? NotificacionReporte::fromArray($data) : null;
    }

    public function findMaquinasByDestinatario(Uuid $idDestinatario): array
    {
        $cacheKey = "notificaciones:maquina:{$idDestinatario->value()}";
        return $this->cache->remember($cacheKey, function () use ($idDestinatario) {
            $conn = $this->db->getConnection();
            $stmt = $conn->prepare("CALL sp_notificaciones_maquina_por_destinatario(?)");
            $stmt->execute([$idDestinatario->value()]);
            
            $notificaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            $this->db->clearPendingResults();
            
            return $notificaciones;
        }, $this->ttl);
    }

    public function findReportesByUsuario(Uuid $idUsuario): array
    {
        $cacheKey = "notificaciones:reporte:{$idUsuario->value()}";
        return $this->cache->remember($cacheKey, function () use ($idUsuario) {
            $conn = $this->db->getConnection();
            $stmt = $conn->prepare("CALL sp_notificaciones_reporte_por_usuario(?)");
            $stmt->execute([$idUsuario->value()]);
            
            $notificaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            $this->db->clearPendingResults();
            
            return $notificaciones;
        }, $this->ttl);
    }

    public function findNoLeidasMaquina(Uuid $idDestinatario): int
    {
        $cacheKey = "notificaciones:no_leidas_maquina:{$idDestinatario->value()}";
        return $this->cache->remember($cacheKey, function () use ($idDestinatario) {
            $conn = $this->db->getConnection();
            $stmt = $conn->prepare("CALL sp_contar_no_leidas_maquina(?, @total)");
            $stmt->execute([$idDestinatario->value()]);
            $stmt->closeCursor();
            
            $result = $conn->query("SELECT @total as total");
            $row = $result->fetch(PDO::FETCH_ASSOC);
            $result->closeCursor();
            $this->db->clearPendingResults();
            
            return (int)($row['total'] ?? 0);
        }, $this->ttl);
    }

    public function findNoLeidasReporte(Uuid $idUsuario): int
    {
        $cacheKey = "notificaciones:no_leidas_reporte:{$idUsuario->value()}";
        return $this->cache->remember($cacheKey, function () use ($idUsuario) {
            $conn = $this->db->getConnection();
            $stmt = $conn->prepare("CALL sp_contar_no_leidas_reporte(?, @total)");
            $stmt->execute([$idUsuario->value()]);
            $stmt->closeCursor();
            
            $result = $conn->query("SELECT @total as total");
            $row = $result->fetch(PDO::FETCH_ASSOC);
            $result->closeCursor();
            $this->db->clearPendingResults();
            
            return (int)($row['total'] ?? 0);
        }, $this->ttl);
    }

    public function marcarLeidaMaquina(Uuid $id): bool
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("CALL sp_marcar_leida_maquina(?)");
        $result = $stmt->execute([$id->value()]);
        $stmt->closeCursor();
        $this->db->clearPendingResults();

        if ($this->cache instanceof \maquinas_recreativas\Infrastructure\Cache\RedisCache) {
            $this->cache->deleteByPattern("notificaciones:maquina:*");
            $this->cache->deleteByPattern("notificaciones:no_leidas*");
        }

        return $result;
    }

    public function marcarLeidaReporte(Uuid $id, Uuid $idUsuario): bool
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("CALL sp_marcar_leida_reporte(?, ?)");
        $result = $stmt->execute([$id->value(), $idUsuario->value()]);
        $stmt->closeCursor();
        $this->db->clearPendingResults();

        $this->cache->delete("notificaciones:reporte:{$idUsuario->value()}");
        $this->cache->delete("notificaciones:no_leidas:{$idUsuario->value()}");
        $this->cache->delete("notificaciones:no_leidas_reporte:{$idUsuario->value()}");

        return $result;
    }

    public function marcarTodasLeidasReporte(Uuid $idUsuario): bool
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("CALL sp_marcar_todas_leidas_reporte(?)");
        $result = $stmt->execute([$idUsuario->value()]);
        $stmt->closeCursor();
        $this->db->clearPendingResults();

        $this->cache->delete("notificaciones:reporte:{$idUsuario->value()}");
        $this->cache->delete("notificaciones:no_leidas:{$idUsuario->value()}");
        $this->cache->delete("notificaciones:no_leidas_reporte:{$idUsuario->value()}");

        return $result;
    }
}