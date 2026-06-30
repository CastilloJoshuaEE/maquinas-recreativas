<?php
/**
 * Infrastructure/Repositories/MySQLNotificacionRepository.php
 * Migrado a PDO. TTL: 120s.
 */
namespace maquinas_recreativas\Infrastructure\Repositories;

use PDO;
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
        $stmt->execute([
            $data['ID_Notificacion'],$data['ID_Remitente'],$data['ID_Destinatario'],
            $data['ID_Maquina'],$data['Tipo'],$data['Mensaje'],$data['Fecha'],$data['Estado']
        ]);
        $this->cache->delete("notificaciones:maquina:{$data['ID_Destinatario']}");
        $this->cache->delete("notificaciones:no_leidas:{$data['ID_Destinatario']}");
    }

    public function saveReporte(NotificacionReporte $notificacion): void
    {
        $conn = $this->db->getConnection();
        $data = $notificacion->toArray();
        $sql  = "INSERT INTO notificaciones (ID_Notificaciones,ID_Reporte,ID_Usuario,mensaje,fecha_hora,leida) VALUES (?,?,?,?,?,?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            $data['ID_Notificaciones'],$data['ID_Reporte'],$data['ID_Usuario'],
            $data['mensaje'],$data['fecha_hora'],$data['leida']
        ]);
        $this->cache->delete("notificaciones:reporte:{$data['ID_Usuario']}");
        $this->cache->delete("notificaciones:no_leidas:{$data['ID_Usuario']}");
    }

    public function findMaquinaById(Uuid $id): ?NotificacionMaquina
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("SELECT * FROM NotificacionMaquinaRecreativa WHERE ID_Notificacion=?");
        $stmt->execute([$id->value()]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $data ? NotificacionMaquina::fromArray($data) : null;
    }

    public function findReporteById(Uuid $id): ?NotificacionReporte
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("SELECT * FROM notificaciones WHERE ID_Notificaciones=?");
        $stmt->execute([$id->value()]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
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
            $stmt->execute([$idDestinatario->value()]);
            $notificaciones = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $notificaciones[] = $row;
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
            $stmt->execute([$idUsuario->value()]);
            $notificaciones = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $notificaciones[] = $row;
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
            $stmt->execute([$idDestinatario->value()]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
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
            $stmt->execute([$idUsuario->value()]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)($row['cantidad'] ?? 0);
        }, $this->ttl);
    }

    public function marcarLeidaMaquina(Uuid $id): bool
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("UPDATE NotificacionMaquinaRecreativa SET Estado='Leido' WHERE ID_Notificacion=?");
        $result = $stmt->execute([$id->value()]);
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

        $check = $conn->prepare("SELECT ID_Notificaciones, leida FROM notificaciones WHERE ID_Notificaciones = ? AND ID_Usuario = ?");
        $check->execute([$idV, $uV]);
        $row = $check->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return false;
        }
        if ($row['leida'] == 1) {
            return true;
        }

        $stmt = $conn->prepare("UPDATE notificaciones SET leida = 1 WHERE ID_Notificaciones = ? AND ID_Usuario = ?");
        $res = $stmt->execute([$idV, $uV]);

        $this->cache->delete("notificaciones:reporte:{$uV}");
        $this->cache->delete("notificaciones:no_leidas:{$uV}");
        $this->cache->delete("notificaciones:no_leidas_reporte:{$uV}");

        return $res;
    }

    public function marcarTodasLeidasReporte(Uuid $idUsuario): bool
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("UPDATE notificaciones SET leida=1 WHERE ID_Usuario=?");
        $v    = $idUsuario->value();
        $res  = $stmt->execute([$v]);
        $this->cache->delete("notificaciones:reporte:{$v}");
        $this->cache->delete("notificaciones:no_leidas:{$v}");
        $this->cache->delete("notificaciones:no_leidas_reporte:{$v}");
        return $res;
    }
}