<?php
/**
 * Infrastructure/Repositories/MySQLHistorialRepository.php
 * TTL: 300 s — crece constantemente, datos semi-frescos aceptables.
 */
namespace maquinas_recreativas\Infrastructure\Repositories;

use maquinas_recreativas\Domain\Historial\HistorialMaquina;
use maquinas_recreativas\Domain\Historial\HistorialActividad;
use maquinas_recreativas\Domain\Historial\HistorialRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Infrastructure\Database\Database;
use maquinas_recreativas\Infrastructure\Cache\CacheInterface;
use maquinas_recreativas\Infrastructure\Cache\CacheFactory;

class MySQLHistorialRepository implements HistorialRepository
{
    private Database       $db;
    private CacheInterface $cache;
    private int            $ttl = 300;

    public function __construct(Database $db, ?CacheInterface $cache = null)
    {
        $this->db    = $db;
        $this->cache = $cache ?? CacheFactory::create();
    }

    // ---- escritura (no se cachea; invalida claves existentes) ---------------

    public function save(HistorialMaquina $historial): void
    {
        $conn = $this->db->getConnection();
        $data = $historial->toArray();
        $sql  = "INSERT INTO historial_maquinas (ID_Maquina,ID_Usuario,tipo_usuario,accion,descripcion,
                     estado_anterior,estado_nuevo,etapa_anterior,etapa_nueva,ip_address,detalles_adicionales,fecha_hora)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?,?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssssssssssss',
            $data['ID_Maquina'],$data['ID_Usuario'],$data['tipo_usuario'],$data['accion'],
            $data['descripcion'],$data['estado_anterior'],$data['estado_nuevo'],
            $data['etapa_anterior'],$data['etapa_nueva'],$data['ip_address'],
            $data['detalles_adicionales'],$data['fecha_hora']);
        $stmt->execute(); $stmt->close();

        // Invalidar
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
        $stmt = $conn->prepare("INSERT INTO historial_actividades (ID_Usuario,descripcion,fecha_registro) VALUES (?,?,?)");
        $stmt->bind_param('sss', $data['ID_Usuario'], $data['descripcion'], $data['fecha_registro']);
        $stmt->execute(); $stmt->close();
        $this->cache->delete("historial:actividades:{$data['ID_Usuario']}");
    }

    // ---- lectura con caché --------------------------------------------------
public function findByMaquina(Uuid $idMaquina, int $limit = 50, int $offset = 0): array
{
    $cacheKey = "historial:maquina:{$idMaquina->value()}:{$limit}:{$offset}";
    return $this->cache->remember($cacheKey, function () use ($idMaquina, $limit, $offset) {
        $conn = $this->db->getConnection();
        $sql  = "SELECT h.*,u.nombre as usuario_nombre,u.apellido as usuario_apellido,u.tipo as usuario_tipo,m.Nombre_Maquina
                 FROM historial_maquinas h
                 INNER JOIN usuario u ON h.ID_Usuario = u.ID_Usuario
                 INNER JOIN MaquinaRecreativa m ON h.ID_Maquina = m.ID_Maquina
                 WHERE h.ID_Maquina = ? 
                 ORDER BY h.fecha_hora DESC 
                 LIMIT ? OFFSET ?";
        $stmt = $conn->prepare($sql);
        $v    = $idMaquina->value();
        $stmt->bind_param('sii', $v, $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        $historial = [];
        while ($row = $result->fetch_assoc()) {
            $historial[] = HistorialMaquina::fromArray($row);
        }
        $result->free();  // ← Liberar el resultado
        $stmt->close();   // ← Cerrar el statement
        // Limpiar resultados pendientes
        while ($conn->more_results() && $conn->next_result()) {
            if ($rs = $conn->store_result()) {
                $rs->free();
            }
        }
        return $historial;
    }, $this->ttl);
}
    public function findByUsuario(Uuid $idUsuario, int $limit = 50, int $offset = 0): array
    {
        $cacheKey = "historial:usuario:{$idUsuario->value()}:{$limit}:{$offset}";
        return $this->cache->remember($cacheKey, function () use ($idUsuario, $limit, $offset) {
            $conn = $this->db->getConnection();
            $sql  = "SELECT h.*,u.nombre as usuario_nombre,u.apellido as usuario_apellido,m.Nombre_Maquina
                     FROM historial_maquinas h
                     INNER JOIN usuario u ON h.ID_Usuario=u.ID_Usuario
                     INNER JOIN MaquinaRecreativa m ON h.ID_Maquina=m.ID_Maquina
                     WHERE h.ID_Usuario=? ORDER BY h.fecha_hora DESC LIMIT ? OFFSET ?";
            $stmt = $conn->prepare($sql);
            $v    = $idUsuario->value();
            $stmt->bind_param('sii', $v, $limit, $offset);
            $stmt->execute();
            $historial = [];
            while ($row = $stmt->get_result()->fetch_assoc()) $historial[] = HistorialMaquina::fromArray($row);
            $stmt->close();
            return $historial;
        }, $this->ttl);
    }

    public function findByAccion(string $accion, int $limit = 100, int $offset = 0): array
    {
        // Búsqueda dinámica — sin caché
        $conn    = $this->db->getConnection();
        $like    = "%{$accion}%";
        $stmt    = $conn->prepare("SELECT * FROM historial_maquinas WHERE accion LIKE ? ORDER BY fecha_hora DESC LIMIT ? OFFSET ?");
        $stmt->bind_param('sii', $like, $limit, $offset);
        $stmt->execute();
        $historial = [];
        while ($row = $stmt->get_result()->fetch_assoc()) $historial[] = HistorialMaquina::fromArray($row);
        $stmt->close();
        return $historial;
    }

    public function findGeneral(
        ?Uuid $idMaquina = null, ?Uuid $idUsuario = null,
        ?string $tipoUsuario = null, ?string $accion = null,
        ?string $fechaInicio = null, ?string $fechaFin = null,
        int $limit = 100, int $offset = 0
    ): array {
        $cacheKey = "historial:general:" . md5(serialize(func_get_args()));
        return $this->cache->remember($cacheKey, function () use ($idMaquina, $idUsuario, $tipoUsuario, $accion, $fechaInicio, $fechaFin, $limit, $offset) {
            $conn   = $this->db->getConnection();
            $sql    = "SELECT h.*,u.nombre as usuario_nombre,u.apellido as usuario_apellido,u.tipo as usuario_tipo,
                              m.Nombre_Maquina,c.Nombre as NombreComercio
                       FROM historial_maquinas h
                       INNER JOIN usuario u ON h.ID_Usuario=u.ID_Usuario
                       INNER JOIN MaquinaRecreativa m ON h.ID_Maquina=m.ID_Maquina
                       LEFT JOIN Comercio c ON m.ID_Comercio=c.ID_Comercio
                       WHERE 1=1";
            $params = []; $types = "";
            if ($idMaquina)    { $sql .= " AND h.ID_Maquina=?";    $params[] = $idMaquina->value();    $types .= "s"; }
            if ($idUsuario)    { $sql .= " AND h.ID_Usuario=?";    $params[] = $idUsuario->value();    $types .= "s"; }
            if ($tipoUsuario)  { $sql .= " AND h.tipo_usuario=?";  $params[] = $tipoUsuario;           $types .= "s"; }
            if ($accion)       { $sql .= " AND h.accion LIKE ?";   $params[] = "%{$accion}%";          $types .= "s"; }
            if ($fechaInicio)  { $sql .= " AND DATE(h.fecha_hora)>=?"; $params[] = $fechaInicio;       $types .= "s"; }
            if ($fechaFin)     { $sql .= " AND DATE(h.fecha_hora)<=?"; $params[] = $fechaFin;          $types .= "s"; }
            $sql .= " ORDER BY h.fecha_hora DESC LIMIT ? OFFSET ?";
            $params[] = $limit; $params[] = $offset; $types .= "ii";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $historial = [];
            while ($row = $stmt->get_result()->fetch_assoc()) $historial[] = HistorialMaquina::fromArray($row);
            $stmt->close();
            return $historial;
        }, $this->ttl);
    }
public function countByFilters(
    ?Uuid $idMaquina = null, ?Uuid $idUsuario = null,
    ?string $tipoUsuario = null, ?string $accion = null,
    ?string $fechaInicio = null, ?string $fechaFin = null
): int {
    $conn   = $this->db->getConnection();
    $sql    = "SELECT COUNT(*) as total FROM historial_maquinas h WHERE 1=1";
    $params = []; $types = "";
    if ($idMaquina)   { $sql .= " AND h.ID_Maquina=?";    $params[] = $idMaquina->value();  $types .= "s"; }
    if ($idUsuario)   { $sql .= " AND h.ID_Usuario=?";    $params[] = $idUsuario->value();  $types .= "s"; }
    if ($tipoUsuario) { $sql .= " AND h.tipo_usuario=?";  $params[] = $tipoUsuario;         $types .= "s"; }
    if ($accion)      { $sql .= " AND h.accion LIKE ?";   $params[] = "%{$accion}%";        $types .= "s"; }
    if ($fechaInicio) { $sql .= " AND DATE(h.fecha_hora)>=?"; $params[] = $fechaInicio;     $types .= "s"; }
    if ($fechaFin)    { $sql .= " AND DATE(h.fecha_hora)<=?"; $params[] = $fechaFin;        $types .= "s"; }
    $stmt = $conn->prepare($sql);
    if (!empty($params)) $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $total = (int)$row['total'];
    $result->free();  // ← Liberar resultado
    $stmt->close();   // ← Cerrar statement
    // Limpiar resultados pendientes
    while ($conn->more_results() && $conn->next_result()) {
        if ($rs = $conn->store_result()) {
            $rs->free();
        }
    }
    return $total;
}
    public function findActividadesByUsuario(Uuid $idUsuario, int $limit = 50): array
    {
        $cacheKey = "historial:actividades:{$idUsuario->value()}";
        return $this->cache->remember($cacheKey, function () use ($idUsuario, $limit) {
            $conn = $this->db->getConnection();
            $stmt = $conn->prepare("SELECT * FROM historial_actividades WHERE ID_Usuario=? ORDER BY fecha_registro DESC LIMIT ?");
            $v    = $idUsuario->value();
            $stmt->bind_param('si', $v, $limit);
            $stmt->execute();
            $actividades = [];
            while ($row = $stmt->get_result()->fetch_assoc()) $actividades[] = HistorialActividad::fromArray($row);
            $stmt->close();
            return $actividades;
        }, $this->ttl);
    }

    public function getResumenReciente(int $limite = 20): array
    {
        $cacheKey = "historial:resumen:{$limite}";
        return $this->cache->remember($cacheKey, function () use ($limite) {
            $historial = $this->findGeneral(null, null, null, null, null, null, $limite, 0);
            $resumen   = ['total' => count($historial), 'por_accion' => [], 'recientes' => []];
            foreach ($historial as $item) {
                $accion = $item->accion();
                $resumen['por_accion'][$accion] = ($resumen['por_accion'][$accion] ?? 0) + 1;
                if (count($resumen['recientes']) < 10) {
                    $resumen['recientes'][] = [
                        'id'          => $item->id()->value(),
                        'accion'      => $accion,
                        'descripcion' => $item->descripcion(),
                        'fecha'       => $item->fechaHora()->format('Y-m-d H:i:s'),
                        'maquina'     => $item->idMaquina()->value(),
                    ];
                }
            }
            return $resumen;
        }, $this->ttl);
    }
}
