<?php
/**
 * Infrastructure/Repositories/PDOHistorialRepository.php
 * 
 * Versión con PDO para procedimientos almacenados
 */
namespace maquinas_recreativas\Infrastructure\Repositories;

use maquinas_recreativas\Domain\Historial\HistorialMaquina;
use maquinas_recreativas\Domain\Historial\HistorialActividad;
use maquinas_recreativas\Domain\Historial\HistorialRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Infrastructure\Database\Database;
use maquinas_recreativas\Infrastructure\Cache\CacheInterface;
use maquinas_recreativas\Infrastructure\Cache\CacheFactory;
use PDO;

class PDOHistorialRepository implements HistorialRepository
{
    private Database $db;
    private CacheInterface $cache;
    private int $ttl = 300;

    public function __construct(Database $db, ?CacheInterface $cache = null)
    {
        $this->db = $db;
        $this->cache = $cache ?? CacheFactory::create();
    }

    // ---- ESCRITURA ----
    public function save(HistorialMaquina $historial): void
    {
        $conn = $this->db->getConnection();
        $data = $historial->toArray();

        $stmt = $conn->prepare("CALL sp_insertar_historial_maquina(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['ID_Maquina'],
            $data['ID_Usuario'],
            $data['tipo_usuario'],
            $data['accion'],
            $data['descripcion'],
            $data['estado_anterior'],
            $data['estado_nuevo'],
            $data['etapa_anterior'],
            $data['etapa_nueva'],
            $data['ip_address'],
            $data['detalles_adicionales']
        ]);
        $stmt->closeCursor();
        $this->db->clearPendingResults();

        // Invalidar caché
        $this->cache->delete("historial:maquina:{$data['ID_Maquina']}:1:50");
        $this->cache->delete("historial:usuario:{$data['ID_Usuario']}:1:50");
        $this->cache->delete("historial:resumen:20");
        if ($this->cache instanceof \maquinas_recreativas\Infrastructure\Cache\RedisCache) {
            $this->cache->deleteByPattern("historial:general:*");
        }
    }

    public function saveActividad(HistorialActividad $actividad): void
    {
        $conn = $this->db->getConnection();
        $data = $actividad->toArray();

        $stmt = $conn->prepare("INSERT INTO historial_actividades (ID_Usuario, descripcion, fecha_registro) VALUES (?, ?, ?)");
        $stmt->execute([$data['ID_Usuario'], $data['descripcion'], $data['fecha_registro']]);
        $stmt->closeCursor();
        $this->db->clearPendingResults();

        $this->cache->delete("historial:actividades:{$data['ID_Usuario']}");
    }

    // ---- LECTURA ----
    public function findByMaquina(Uuid $idMaquina, int $limit = 50, int $offset = 0): array
    {
        $cacheKey = "historial:maquina:{$idMaquina->value()}:{$limit}:{$offset}";
        return $this->cache->remember($cacheKey, function () use ($idMaquina, $limit, $offset) {
            $conn = $this->db->getConnection();
            $stmt = $conn->prepare("CALL sp_historial_por_maquina(?, ?, ?)");
            $stmt->execute([$idMaquina->value(), $limit, $offset]);
            
            $historial = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $historial[] = HistorialMaquina::fromArray($row);
            }
            $stmt->closeCursor();
            $this->db->clearPendingResults();
            
            return $historial;
        }, $this->ttl);
    }

    public function findByUsuario(Uuid $idUsuario, int $limit = 50, int $offset = 0): array
    {
        $cacheKey = "historial:usuario:{$idUsuario->value()}:{$limit}:{$offset}";
        return $this->cache->remember($cacheKey, function () use ($idUsuario, $limit, $offset) {
            $conn = $this->db->getConnection();
            $stmt = $conn->prepare("CALL sp_historial_por_usuario(?, ?, ?)");
            $stmt->execute([$idUsuario->value(), $limit, $offset]);
            
            $historial = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $historial[] = HistorialMaquina::fromArray($row);
            }
            $stmt->closeCursor();
            $this->db->clearPendingResults();
            
            return $historial;
        }, $this->ttl);
    }

    public function findByAccion(string $accion, int $limit = 100, int $offset = 0): array
    {
        // Búsqueda dinámica - sin caché
        $conn = $this->db->getConnection();
        $like = "%{$accion}%";
        $sql = "SELECT * FROM historial_maquinas WHERE accion LIKE ? ORDER BY fecha_hora DESC LIMIT ? OFFSET ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$like, $limit, $offset]);
        
        $historial = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $historial[] = HistorialMaquina::fromArray($row);
        }
        $stmt->closeCursor();
        $this->db->clearPendingResults();
        
        return $historial;
    }

    public function findGeneral(
        ?Uuid $idMaquina = null,
        ?Uuid $idUsuario = null,
        ?string $tipoUsuario = null,
        ?string $accion = null,
        ?string $fechaInicio = null,
        ?string $fechaFin = null,
        int $limit = 100,
        int $offset = 0
    ): array {
        $cacheKey = "historial:general:" . md5(serialize(func_get_args()));
        return $this->cache->remember($cacheKey, function () use ($idMaquina, $idUsuario, $tipoUsuario, $accion, $fechaInicio, $fechaFin, $limit, $offset) {
            $conn = $this->db->getConnection();
            
            $idMaquinaVal = $idMaquina ? $idMaquina->value() : null;
            $idUsuarioVal = $idUsuario ? $idUsuario->value() : null;

            $stmt = $conn->prepare("CALL sp_historial_general(?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $idMaquinaVal,
                $idUsuarioVal,
                $tipoUsuario,
                $accion,
                $fechaInicio,
                $fechaFin,
                $limit,
                $offset
            ]);
            
            $historial = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $historial[] = HistorialMaquina::fromArray($row);
            }
            $stmt->closeCursor();
            $this->db->clearPendingResults();
            
            return $historial;
        }, $this->ttl);
    }

    public function countByFilters(
        ?Uuid $idMaquina = null,
        ?Uuid $idUsuario = null,
        ?string $tipoUsuario = null,
        ?string $accion = null,
        ?string $fechaInicio = null,
        ?string $fechaFin = null
    ): int {
        $conn = $this->db->getConnection();
        
        $idMaquinaVal = $idMaquina ? $idMaquina->value() : null;
        $idUsuarioVal = $idUsuario ? $idUsuario->value() : null;

        $stmt = $conn->prepare("CALL sp_contar_historial(?, ?, ?, ?, ?, ?, @total)");
        $stmt->execute([
            $idMaquinaVal,
            $idUsuarioVal,
            $tipoUsuario,
            $accion,
            $fechaInicio,
            $fechaFin
        ]);
        $stmt->closeCursor();
        
        $result = $conn->query("SELECT @total as total");
        $row = $result->fetch(PDO::FETCH_ASSOC);
        $result->closeCursor();
        $this->db->clearPendingResults();
        
        return (int)($row['total'] ?? 0);
    }

    public function findActividadesByUsuario(Uuid $idUsuario, int $limit = 50): array
    {
        $cacheKey = "historial:actividades:{$idUsuario->value()}";
        return $this->cache->remember($cacheKey, function () use ($idUsuario, $limit) {
            $conn = $this->db->getConnection();
            $stmt = $conn->prepare("SELECT * FROM historial_actividades WHERE ID_Usuario = ? ORDER BY fecha_registro DESC LIMIT ?");
            $stmt->execute([$idUsuario->value(), $limit]);
            
            $actividades = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $actividades[] = HistorialActividad::fromArray($row);
            }
            $stmt->closeCursor();
            $this->db->clearPendingResults();
            
            return $actividades;
        }, $this->ttl);
    }

    public function getResumenReciente(int $limite = 20): array
    {
        $cacheKey = "historial:resumen:{$limite}";
        return $this->cache->remember($cacheKey, function () use ($limite) {
            $conn = $this->db->getConnection();
            $stmt = $conn->prepare("CALL sp_resumen_historial_reciente(?)");
            $stmt->execute([$limite]);
            
            $historial = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $historial[] = HistorialMaquina::fromArray($row);
            }
            $stmt->closeCursor();
            $this->db->clearPendingResults();

            $resumen = ['total' => count($historial), 'por_accion' => [], 'recientes' => []];
            foreach ($historial as $item) {
                $accion = $item->accion();
                $resumen['por_accion'][$accion] = ($resumen['por_accion'][$accion] ?? 0) + 1;
                if (count($resumen['recientes']) < 10) {
                    $resumen['recientes'][] = [
                        'id' => $item->id()->value(),
                        'accion' => $accion,
                        'descripcion' => $item->descripcion(),
                        'fecha' => $item->fechaHora()->format('Y-m-d H:i:s'),
                        'maquina' => $item->idMaquina()->value(),
                    ];
                }
            }
            return $resumen;
        }, $this->ttl);
    }
}