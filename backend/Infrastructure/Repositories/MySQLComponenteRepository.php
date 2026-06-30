<?php
/**
 * Infrastructure/Repositories/MySQLComponenteRepository.php
 * Migrado a PDO. UUID() de SQL reemplazado por generación en PHP (portable).
 */
namespace maquinas_recreativas\Infrastructure\Repositories;

use PDO;
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
        $idValue = $data['ID_Componente'];

        $checkStmt = $conn->prepare("SELECT COUNT(*) as total FROM componente WHERE ID_Componente=?");
        $checkStmt->execute([$idValue]);
        $exists = $checkStmt->fetch(PDO::FETCH_ASSOC)['total'] > 0;

        if ($exists) {
            $stmt = $conn->prepare("UPDATE componente SET tipo=?, nombre=?, precio=? WHERE ID_Componente=?");
            $stmt->execute([$data['tipo'], $data['nombre'], $data['precio'], $idValue]);
        } else {
            $stmt = $conn->prepare("INSERT INTO componente (ID_Componente, tipo, nombre, precio) VALUES (?, ?, ?, ?)");
            $stmt->execute([$idValue, $data['tipo'], $data['nombre'], $data['precio']]);
        }

        $this->saveAsignacion($componente);

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
        $idComponente = $componente->id()->value();

        if ($componente->estaAsignado() && $componente->fechaAsignacion() !== null) {
            $checkStmt = $conn->prepare("SELECT ID_Registro FROM componente_usuario WHERE ID_Componente = ? AND fecha_liberacion IS NULL");
            $checkStmt->execute([$idComponente]);
            $existsActive = (bool) $checkStmt->fetch(PDO::FETCH_ASSOC);

            if (!$existsActive) {
                $uid = $componente->usuarioAsignado()?->value();
                $mid = $componente->maquinaAsignada()?->value();
                $fec = $componente->fechaAsignacion()->format('Y-m-d H:i:s');
                $idRegistro = Uuid::v4()->value();

                $stmt = $conn->prepare("INSERT INTO componente_usuario (ID_Registro, ID_Componente, ID_Usuario, ID_Maquina, fecha_asignacion) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$idRegistro, $idComponente, $uid, $mid, $fec]);
            }
        } elseif ($componente->fechaLiberacion() !== null) {
            $stmt = $conn->prepare("UPDATE componente_usuario SET fecha_liberacion = ? WHERE ID_Componente = ? AND fecha_liberacion IS NULL");
            $fec = $componente->fechaLiberacion()->format('Y-m-d H:i:s');
            $stmt->execute([$fec, $idComponente]);
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
            $stmt->execute([$id->value()]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $data ? Componente::fromArray($data) : null;
        }, 1800);
    }

    public function findByTipo(?TipoComponente $tipo = null, int $limit = 10, int $offset = 0): array
    {
        $conn = $this->db->getConnection();

        $sql = "SELECT c.*, cu.ID_Usuario as usuario_uso, cu.ID_Maquina as maquina_uso,
                       cu.fecha_asignacion, cu.fecha_liberacion
                FROM componente c
                LEFT JOIN componente_usuario cu ON c.ID_Componente = cu.ID_Componente AND cu.fecha_liberacion IS NULL
                WHERE 1=1";

        $params = [];

        if ($tipo) {
            $sql .= " AND c.tipo = ?";
            $params[] = $tipo->value();
        }

        $sql .= " GROUP BY c.ID_Componente LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);

        $componentes = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $componentes[] = Componente::fromArray($row);
        }
        return $componentes;
    }

    public function findDisponibles(?TipoComponente $tipo = null): array
    {
        $conn = $this->db->getConnection();

        $sql = "SELECT c.* FROM componente c
                LEFT JOIN componente_usuario cu ON c.ID_Componente = cu.ID_Componente AND cu.fecha_liberacion IS NULL
                WHERE cu.ID_Componente IS NULL";

        $params = [];

        if ($tipo) {
            $sql .= " AND c.tipo = ?";
            $params[] = $tipo->value();
        }

        $sql .= " ORDER BY c.nombre ASC";

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);

        $componentes = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $componentes[] = Componente::fromArray($row);
        }
        return $componentes;
    }

    public function findEnUsoPorUsuario(Uuid $idUsuario, ?Uuid $idMaquina = null): array
    {
        $conn = $this->db->getConnection();

        $sql = "SELECT c.*, cu.fecha_asignacion, cu.ID_Maquina as maquina_uso, m.Nombre_Maquina
                FROM componente_usuario cu
                INNER JOIN componente c ON cu.ID_Componente = c.ID_Componente
                LEFT JOIN MaquinaRecreativa m ON cu.ID_Maquina = m.ID_Maquina
                WHERE cu.ID_Usuario = ? AND cu.fecha_liberacion IS NULL";

        $params = [$idUsuario->value()];

        if ($idMaquina) {
            $sql .= " AND cu.ID_Maquina = ?";
            $params[] = $idMaquina->value();
        }

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            error_log("Error preparando findEnUsoPorUsuario");
            return [];
        }
        $stmt->execute($params);

        $componentes = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $componentes[] = Componente::fromArray($row);
        }
        return $componentes;
    }

    public function countByTipo(?TipoComponente $tipo = null): int
    {
        $conn = $this->db->getConnection();
        if ($tipo) {
            $stmt = $conn->prepare("SELECT COUNT(*) as total FROM componente WHERE tipo=?");
            $stmt->execute([$tipo->value()]);
        } else {
            $stmt = $conn->prepare("SELECT COUNT(*) as total FROM componente");
            $stmt->execute();
        }
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$row['total'];
    }

    public function generarNumeroPlaca(): string
    {
        $conn    = $this->db->getConnection();
        $anio    = date('y');
        $prefijo = "PL{$anio}";
        $like    = $prefijo . '%';
        // SUBSTRING funciona igual en ambos motores; CAST(... AS UNSIGNED) es MySQL-only,
        // se reemplaza por CAST(... AS INTEGER) que es válido en ambos.
        $sql = "SELECT MAX(CAST(SUBSTRING(nombre,5) AS INTEGER)) as max_seq FROM componente WHERE nombre LIKE ? AND tipo='Logistico'";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$like]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $sec = ($row['max_seq'] ?? 0) + 1;
        return $prefijo . str_pad((string)$sec, 3, '0', STR_PAD_LEFT);
    }
}