<?php
/**
 * Infrastructure/Repositories/MySQLComponenteRepository.php
 * TTL: 1800 s disponibles, 600 s disponibles (cambian más), 300 s en uso
 */
namespace maquinas_recreativas\Infrastructure\Repositories;

use maquinas_recreativas\Domain\Componente\Componente;
use maquinas_recreativas\Domain\Componente\ComponenteRepository;
use maquinas_recreativas\Domain\Componente\TipoComponente;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Infrastructure\Database\Database;
use maquinas_recreativas\Infrastructure\Cache\CacheInterface;
use maquinas_recreativas\Infrastructure\Cache\CacheFactory;

class MySQLComponenteRepository implements ComponenteRepository
{
    private Database       $db;
    private CacheInterface $cache;

    public function __construct(Database $db, ?CacheInterface $cache = null)
    {
        $this->db    = $db;
        $this->cache = $cache ?? CacheFactory::create();
    }

    public function save(Componente $componente): void
    {
        $conn = $this->db->getConnection();
        $data = $componente->toArray();
        $checkStmt = $conn->prepare("SELECT COUNT(*) as total FROM componente WHERE ID_Componente=?");
        $idValue   = $data['ID_Componente'];
        $checkStmt->bind_param('s', $idValue);
        $checkStmt->execute();
        $exists = $checkStmt->get_result()->fetch_assoc()['total'] > 0;
        $checkStmt->close();

        if ($exists) {
            $stmt = $conn->prepare("UPDATE componente SET tipo=?,nombre=?,precio=? WHERE ID_Componente=?");
            $stmt->bind_param('ssds', $data['tipo'], $data['nombre'], $data['precio'], $idValue);
        } else {
            $stmt = $conn->prepare("INSERT INTO componente (ID_Componente,tipo,nombre,precio) VALUES (?,?,?,?)");
            $stmt->bind_param('sssd', $idValue, $data['tipo'], $data['nombre'], $data['precio']);
        }
        $stmt->execute(); $stmt->close();
        $this->saveAsignacion($componente);

        // Invalidar
        $this->cache->delete("componente:id:{$idValue}");
        if ($this->cache instanceof \maquinas_recreativas\Infrastructure\Cache\RedisCache) {
            $this->cache->deleteByPattern("componentes:tipo:*");
            $this->cache->deleteByPattern("componentes:disponibles:*");
        }
        if ($componente->usuarioAsignado()) {
            $this->cache->delete("componentes:en_uso:{$componente->usuarioAsignado()->value()}:");
        }
    }

    private function saveAsignacion(Componente $componente): void
    {
        $conn = $this->db->getConnection();
        $id   = $componente->id()->value();
        if ($componente->estaAsignado() && $componente->fechaAsignacion() !== null) {
            $checkStmt = $conn->prepare("SELECT ID_Registro FROM componente_usuario WHERE ID_Componente=? AND fecha_liberacion IS NULL");
            $checkStmt->bind_param('s', $id);
            $checkStmt->execute();
            $existsActive = $checkStmt->get_result()->num_rows > 0;
            $checkStmt->close();
            if (!$existsActive) {
                $stmt = $conn->prepare("INSERT INTO componente_usuario (ID_Registro,ID_Componente,ID_Usuario,ID_Maquina,fecha_asignacion) VALUES (UUID(),?,?,?,?)");
                $uid  = $componente->usuarioAsignado()?->value();
                $mid  = $componente->maquinaAsignada()?->value();
                $fec  = $componente->fechaAsignacion()->format('Y-m-d H:i:s');
                $stmt->bind_param('ssss', $id, $uid, $mid, $fec);
                $stmt->execute(); $stmt->close();
            }
        } elseif ($componente->fechaLiberacion() !== null) {
            $stmt = $conn->prepare("UPDATE componente_usuario SET fecha_liberacion=? WHERE ID_Componente=? AND fecha_liberacion IS NULL");
            $fec  = $componente->fechaLiberacion()->format('Y-m-d H:i:s');
            $stmt->bind_param('ss', $fec, $id);
            $stmt->execute(); $stmt->close();
        }
    }

    public function findById(Uuid $id): ?Componente
    {
        $cacheKey = "componente:id:{$id->value()}";
        return $this->cache->remember($cacheKey, function () use ($id) {
            $conn = $this->db->getConnection();
            $sql  = "SELECT c.*, cu.ID_Usuario as usuario_uso, cu.ID_Maquina as maquina_uso,
                            cu.fecha_asignacion, cu.fecha_liberacion
                     FROM componente c
                     LEFT JOIN componente_usuario cu ON c.ID_Componente=cu.ID_Componente AND cu.fecha_liberacion IS NULL
                     WHERE c.ID_Componente=?";
            $stmt = $conn->prepare($sql);
            $v    = $id->value();
            $stmt->bind_param('s', $v);
            $stmt->execute();
            $data = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $data ? Componente::fromArray($data) : null;
        }, 1800);
    }

    public function findByTipo(?TipoComponente $tipo = null, int $limit = 10, int $offset = 0): array
    {
        $tipoStr  = $tipo ? $tipo->value() : 'all';
        $cacheKey = "componentes:tipo:{$tipoStr}:{$limit}:{$offset}";
        return $this->cache->remember($cacheKey, function () use ($tipo, $limit, $offset) {
            $conn   = $this->db->getConnection();
            $sql    = "SELECT c.*, cu.ID_Usuario as usuario_uso, cu.ID_Maquina as maquina_uso,
                              cu.fecha_asignacion, cu.fecha_liberacion
                       FROM componente c
                       LEFT JOIN componente_usuario cu ON c.ID_Componente=cu.ID_Componente AND cu.fecha_liberacion IS NULL
                       WHERE 1=1";
            $params = []; $types = "";
            if ($tipo) { $sql .= " AND c.tipo=?"; $params[] = $tipo->value(); $types .= "s"; }
            $sql .= " GROUP BY c.ID_Componente LIMIT ? OFFSET ?";
            $params[] = $limit; $params[] = $offset; $types .= "ii";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $componentes = [];
            while ($row = $stmt->get_result()->fetch_assoc()) $componentes[] = Componente::fromArray($row);
            $stmt->close();
            return $componentes;
        }, 1800);
    }

    public function findDisponibles(?TipoComponente $tipo = null): array
    {
        $tipoStr  = $tipo ? $tipo->value() : 'all';
        $cacheKey = "componentes:disponibles:{$tipoStr}";
        return $this->cache->remember($cacheKey, function () use ($tipo) {
            $conn   = $this->db->getConnection();
            $sql    = "SELECT c.* FROM componente c
                       LEFT JOIN componente_usuario cu ON c.ID_Componente=cu.ID_Componente AND cu.fecha_liberacion IS NULL
                       WHERE cu.ID_Componente IS NULL";
            $params = []; $types = "";
            if ($tipo) { $sql .= " AND c.tipo=?"; $params[] = $tipo->value(); $types .= "s"; }
            $sql .= " ORDER BY c.nombre ASC";
            $stmt = $conn->prepare($sql);
            if (!empty($params)) $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $componentes = [];
            while ($row = $stmt->get_result()->fetch_assoc()) $componentes[] = Componente::fromArray($row);
            $stmt->close();
            return $componentes;
        }, 600);
    }

    public function findEnUsoPorUsuario(Uuid $idUsuario, ?Uuid $idMaquina = null): array
    {
        $mid      = $idMaquina ? $idMaquina->value() : '';
        $cacheKey = "componentes:en_uso:{$idUsuario->value()}:{$mid}";
        return $this->cache->remember($cacheKey, function () use ($idUsuario, $idMaquina) {
            $conn   = $this->db->getConnection();
            $sql    = "SELECT c.*, cu.fecha_asignacion, cu.ID_Maquina as maquina_uso, m.Nombre_Maquina
                       FROM componente_usuario cu
                       INNER JOIN componente c ON cu.ID_Componente=c.ID_Componente
                       LEFT JOIN MaquinaRecreativa m ON cu.ID_Maquina=m.ID_Maquina
                       WHERE cu.ID_Usuario=? AND cu.fecha_liberacion IS NULL";
            $params = [$idUsuario->value()]; $types = "s";
            if ($idMaquina) { $sql .= " AND cu.ID_Maquina=?"; $params[] = $idMaquina->value(); $types .= "s"; }
            $stmt = $conn->prepare($sql);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $componentes = [];
            while ($row = $stmt->get_result()->fetch_assoc()) $componentes[] = Componente::fromArray($row);
            $stmt->close();
            return $componentes;
        }, 300);
    }

    public function countByTipo(?TipoComponente $tipo = null): int
    {
        $conn = $this->db->getConnection();
        if ($tipo) {
            $stmt = $conn->prepare("SELECT COUNT(*) as total FROM componente WHERE tipo=?");
            $v    = $tipo->value(); $stmt->bind_param('s', $v);
        } else {
            $stmt = $conn->prepare("SELECT COUNT(*) as total FROM componente");
        }
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return (int)$row['total'];
    }

    public function generarNumeroPlaca(): string
    {
        $conn    = $this->db->getConnection();
        $anio    = date('y');
        $prefijo = "PL{$anio}";
        $like    = $prefijo . '%';
        $stmt    = $conn->prepare("SELECT MAX(CAST(SUBSTRING(nombre,5) AS UNSIGNED)) as max_seq FROM componente WHERE nombre LIKE ? AND tipo='Logistico'");
        $stmt->bind_param('s', $like);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        $sec = ($row['max_seq'] ?? 0) + 1;
        return $prefijo . str_pad((string)$sec, 3, '0', STR_PAD_LEFT);
    }
}
