<?php
/**
 * Infrastructure/Repositories/PDOComponenteRepository.php
 */
namespace maquinas_recreativas\Infrastructure\Repositories;

use maquinas_recreativas\Domain\Componente\Componente;
use maquinas_recreativas\Domain\Componente\ComponenteRepository;
use maquinas_recreativas\Domain\Componente\TipoComponente;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Infrastructure\Database\Database;
use maquinas_recreativas\Infrastructure\Cache\CacheInterface;
use maquinas_recreativas\Infrastructure\Cache\CacheFactory;
use PDO;

class PDOComponenteRepository implements ComponenteRepository
{
    private Database $db;
    private CacheInterface $cache;

    public function __construct(Database $db, ?CacheInterface $cache = null)
    {
        $this->db = $db;
        $this->cache = $cache ?? CacheFactory::create();
    }

    public function save(Componente $componente): void
    {
        $conn = $this->db->getConnection();
        $data = $componente->toArray();
        $idValue = $data['ID_Componente'];

        // Insertar/Actualizar componente
        $checkStmt = $conn->prepare("SELECT COUNT(*) as total FROM componente WHERE ID_Componente = ?");
        $checkStmt->execute([$idValue]);
        $exists = $checkStmt->fetchColumn() > 0;
        $checkStmt->closeCursor();

        if ($exists) {
            $stmt = $conn->prepare("UPDATE componente SET tipo = ?, nombre = ?, precio = ? WHERE ID_Componente = ?");
            $stmt->execute([$data['tipo'], $data['nombre'], $data['precio'], $idValue]);
        } else {
            $stmt = $conn->prepare("INSERT INTO componente (ID_Componente, tipo, nombre, precio) VALUES (?, ?, ?, ?)");
            $stmt->execute([$idValue, $data['tipo'], $data['nombre'], $data['precio']]);
        }
        $stmt->closeCursor();

        // Gestionar asignación usando SPs
        $this->saveAsignacion($componente);

        // Invalidar caché
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
            // Verificar si ya tiene asignación activa
            $check = $conn->prepare("SELECT ID_Registro FROM componente_usuario WHERE ID_Componente = ? AND fecha_liberacion IS NULL");
            $check->execute([$idComponente]);
            $exists = $check->fetchColumn() > 0;
            $check->closeCursor();

            if (!$exists) {
                $uid = $componente->usuarioAsignado()?->value();
                $mid = $componente->maquinaAsignada()?->value();
                $fec = $componente->fechaAsignacion()->format('Y-m-d H:i:s');

                $stmt = $conn->prepare("CALL sp_asignar_componente(?, ?, ?, ?)");
                $stmt->execute([$idComponente, $uid, $mid, $fec]);
                $stmt->closeCursor();
                $this->db->clearPendingResults();
            }
        } elseif ($componente->fechaLiberacion() !== null) {
            $fec = $componente->fechaLiberacion()->format('Y-m-d H:i:s');
            $stmt = $conn->prepare("CALL sp_liberar_componente(?, ?)");
            $stmt->execute([$idComponente, $fec]);
            $stmt->closeCursor();
            $this->db->clearPendingResults();
        }
    }

    public function findById(Uuid $id): ?Componente
    {
        $cacheKey = "componente:id:{$id->value()}";
        return $this->cache->remember($cacheKey, function () use ($id) {
            $conn = $this->db->getConnection();
            $sql = "SELECT c.*, cu.ID_Usuario as usuario_uso, cu.ID_Maquina as maquina_uso,
                           cu.fecha_asignacion, cu.fecha_liberacion
                    FROM componente c
                    LEFT JOIN componente_usuario cu ON c.ID_Componente = cu.ID_Componente AND cu.fecha_liberacion IS NULL
                    WHERE c.ID_Componente = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$id->value()]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            $this->db->clearPendingResults();
            
            return $data ? Componente::fromArray($data) : null;
        }, 1800);
    }

    public function findByTipo(?TipoComponente $tipo = null, int $limit = 10, int $offset = 0): array
    {
        $conn = $this->db->getConnection();
        $tipoVal = $tipo ? $tipo->value() : null;

        $stmt = $conn->prepare("CALL sp_listar_componentes(?, ?, ?)");
        $stmt->execute([$tipoVal, $limit, $offset]);
        
        $componentes = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $componentes[] = Componente::fromArray($row);
        }
        $stmt->closeCursor();
        $this->db->clearPendingResults();
        
        return $componentes;
    }

    public function findDisponibles(?TipoComponente $tipo = null): array
    {
        $conn = $this->db->getConnection();
        $tipoVal = $tipo ? $tipo->value() : null;

        $stmt = $conn->prepare("CALL sp_componentes_disponibles(?)");
        $stmt->execute([$tipoVal]);
        
        $componentes = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $componentes[] = Componente::fromArray($row);
        }
        $stmt->closeCursor();
        $this->db->clearPendingResults();
        
        return $componentes;
    }

    public function findEnUsoPorUsuario(Uuid $idUsuario, ?Uuid $idMaquina = null): array
    {
        $conn = $this->db->getConnection();
        $uid = $idUsuario->value();
        $mid = $idMaquina ? $idMaquina->value() : null;

        $stmt = $conn->prepare("CALL sp_componentes_en_uso_por_usuario(?, ?)");
        $stmt->execute([$uid, $mid]);
        
        $componentes = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $componentes[] = Componente::fromArray($row);
        }
        $stmt->closeCursor();
        $this->db->clearPendingResults();
        
        return $componentes;
    }

    public function countByTipo(?TipoComponente $tipo = null): int
    {
        $conn = $this->db->getConnection();
        $tipoVal = $tipo ? $tipo->value() : null;
        
        $stmt = $conn->prepare("CALL sp_contar_componentes_por_tipo(?, @total)");
        $stmt->execute([$tipoVal]);
        $stmt->closeCursor();
        
        $result = $conn->query("SELECT @total as total");
        $row = $result->fetch(PDO::FETCH_ASSOC);
        $result->closeCursor();
        $this->db->clearPendingResults();
        
        return (int)($row['total'] ?? 0);
    }

    public function generarNumeroPlaca(): string
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("CALL sp_generar_numero_placa(@placa)");
        $stmt->execute();
        $stmt->closeCursor();
        
        $result = $conn->query("SELECT @placa as placa");
        $row = $result->fetch(PDO::FETCH_ASSOC);
        $result->closeCursor();
        $this->db->clearPendingResults();
        
        return $row['placa'] ?? '';
    }
}