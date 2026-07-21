<?php
/**
 * Infrastructure/Repositories/PDOReporteRepository.php
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
use PDO;

class PDOReporteRepository implements ReporteRepository
{
    private Database $db;
    private CacheInterface $cache;
    private int $ttl = 120;

    public function __construct(Database $db, ?CacheInterface $cache = null)
    {
        $this->db = $db;
        $this->cache = $cache ?? CacheFactory::create();
    }

    public function save(Reporte $reporte): void
    {
        $data = $reporte->toArray();

        $idReporte = $data['ID_Reporte'];
        $idEmisor = $data['ID_Usuario_Emisor'];
        $idDestinatario = $data['ID_Usuario_Destinatario'] ?? '';
        $descripcion = $data['descripcion'];
        $fechaHora = $data['fecha_hora'];
        $estado = $data['estado'];

        if (empty($idEmisor)) {
            throw new \Exception('El emisor del reporte no puede ser nulo');
        }

        $stmt = $this->db->prepareCall('CALL sp_insertar_reporte(?, ?, ?, ?, ?, ?)');
        $stmt->execute([$idReporte, $idEmisor, $idDestinatario, $descripcion, $fechaHora, $estado]);
        $stmt->closeCursor();

        $this->cache->delete("reporte:id:{$idReporte}");
        $this->cache->delete("reportes:usuario:{$idEmisor}");
        if (!empty($idDestinatario)) {
            $this->cache->delete("reportes:usuario:{$idDestinatario}");
            $this->invalidateChat($idEmisor, $idDestinatario);
        } else {
            if ($this->cache instanceof \maquinas_recreativas\Infrastructure\Cache\RedisCache) {
                $this->cache->deleteByPattern('reportes:usuario:*');
            }
        }
    }

    private function invalidateChat(string $uid1, string $uid2): void
    {
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
            $stmt = $this->db->prepareCall('CALL sp_buscar_reporte_por_id(?)');
            $stmt->execute([$id->value()]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

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
            $stmt = $this->db->prepareCall('CALL sp_reportes_por_usuario(?)');
            $stmt->execute([$idUsuario->value()]);

            $reportes = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if (!empty($row['emisor_email'])) {
                    $row['emisor_email'] = CifradoHelper::desencriptar($row['emisor_email']);
                }
                if (!empty($row['destinatario_email'])) {
                    $row['destinatario_email'] = CifradoHelper::desencriptar($row['destinatario_email']);
                }
                $reportes[] = Reporte::fromArray($row);
            }
            $stmt->closeCursor();

            return $reportes;
        }, $this->ttl);
    }

    public function findChat(Uuid $emisorId, Uuid $destinatarioId): array
    {
        $cacheKey = "reportes:chat:{$emisorId->value()}:{$destinatarioId->value()}";
        return $this->cache->remember($cacheKey, function () use ($emisorId, $destinatarioId) {
            $stmt = $this->db->prepareCall('CALL sp_chat_entre_usuarios(?, ?)');
            $stmt->execute([$emisorId->value(), $destinatarioId->value()]);

            $reportes = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if (!empty($row['emisor_email'])) {
                    $row['emisor_email'] = CifradoHelper::desencriptar($row['emisor_email']);
                }
                if (!empty($row['destinatario_email'])) {
                    $row['destinatario_email'] = CifradoHelper::desencriptar($row['destinatario_email']);
                }
                $reportes[] = Reporte::fromArray($row);
            }
            $stmt->closeCursor();

            return $reportes;
        }, $this->ttl);
    }

    public function findUsuariosChat(Uuid $idUsuario): array
    {
        $cacheKey = "reportes:usuarios_chat:{$idUsuario->value()}";
        return $this->cache->remember($cacheKey, function () use ($idUsuario) {
            $stmt = $this->db->prepareCall('CALL sp_usuarios_chat(?)');
            $stmt->execute([$idUsuario->value()]);

            $usuarios = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if (!empty($row['email'])) {
                    $row['email'] = CifradoHelper::desencriptar($row['email']);
                }
                $usuarios[] = $row;
            }
            $stmt->closeCursor();

            return $usuarios;
        }, $this->ttl);
    }

    public function updateEstado(Uuid $id, EstadoReporte $estado): bool
    {
        $stmt = $this->db->prepareCall('CALL sp_actualizar_estado_reporte(?, ?)');
        $result = $stmt->execute([$id->value(), $estado->value()]);
        $stmt->closeCursor();

        $this->cache->delete("reporte:id:{$id->value()}");
        if ($this->cache instanceof \maquinas_recreativas\Infrastructure\Cache\RedisCache) {
            $this->cache->deleteByPattern('reportes:usuario:*');
        }

        return $result;
    }
}