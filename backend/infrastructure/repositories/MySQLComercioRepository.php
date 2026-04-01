<?php
/**
 * infrastructure/repositories/MySQLComercioRepository.php
 *
 * Implementación MySQL del repositorio de comercios.
 *
 * @package maquinas_recreativas\Infrastructure\Repositories
 */

namespace maquinas_recreativas\Infrastructure\Repositories;

use maquinas_recreativas\Domain\Comercio\Comercio;
use maquinas_recreativas\Domain\Comercio\ComercioRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Infrastructure\Database\Database;
use maquinas_recreativas\Infrastructure\Security\CifradoHelper;

/**
 * Class MySQLComercioRepository
 */
class MySQLComercioRepository implements ComercioRepository
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * {@inheritdoc}
     */
    public function guardar(Comercio $comercio): void
    {
        $conn = $this->db->getConnection();
        $data = $comercio->toArray();

        // Verificar si existe
        $checkSql = "SELECT COUNT(*) as total FROM Comercio WHERE ID_Comercio = ?";
        $checkStmt = $conn->prepare($checkSql);
        $idValue = $data['id'];
        $checkStmt->bind_param('s', $idValue);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        $exists = $checkResult->fetch_assoc()['total'] > 0;
        $checkStmt->close();

        if ($exists) {
            $sql = "UPDATE Comercio SET 
                        Nombre = ?,
                        Tipo = ?,
                        Direccion = ?,
                        Telefono = ?
                    WHERE ID_Comercio = ?";
            
            $stmt = $conn->prepare($sql);
            $nombre = $data['nombre'];
            $tipo = $data['tipo'];
            $direccion = $data['direccion'];
            $telefono = $data['telefono'];
            $stmt->bind_param('sssss', $nombre, $tipo, $direccion, $telefono, $idValue);
        } else {
            $sql = "INSERT INTO Comercio (ID_Comercio, Nombre, Tipo, Direccion, Telefono, Fecha_Registro) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sql);
            $nombre = $data['nombre'];
            $tipo = $data['tipo'];
            $direccion = $data['direccion'];
            $telefono = $data['telefono'];
            $fechaRegistro = $data['fecha_registro'];
            $stmt->bind_param('ssssss', $idValue, $nombre, $tipo, $direccion, $telefono, $fechaRegistro);
        }
        
        $stmt->execute();
        $stmt->close();
    }

    /**
     * {@inheritdoc}
     */
    public function buscarPorId(string $id): ?Comercio
    {
        $conn = $this->db->getConnection();
        $sql = "SELECT * FROM Comercio WHERE ID_Comercio = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();

        return $data ? Comercio::fromArray($data) : null;
    }

    /**
     * {@inheritdoc}
     */
    public function buscarPorNombre(string $nombre): ?Comercio
    {
        $conn = $this->db->getConnection();
        $sql = "SELECT * FROM Comercio WHERE Nombre = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $nombre);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();

        return $data ? Comercio::fromArray($data) : null;
    }

    /**
     * {@inheritdoc}
     */
    public function obtenerTodos(array $criterios = []): array
    {
        $conn = $this->db->getConnection();
        $sql = "SELECT c.*, COUNT(m.ID_Maquina) as cantidad_maquinas 
                FROM Comercio c
                LEFT JOIN MaquinaRecreativa m ON c.ID_Comercio = m.ID_Comercio
                WHERE 1=1";
        
        $params = [];
        $types = "";
        
        if (!empty($criterios['tipo'])) {
            $sql .= " AND c.Tipo = ?";
            $params[] = $criterios['tipo'];
            $types .= "s";
        }
        
        if (!empty($criterios['nombre'])) {
            $sql .= " AND c.Nombre LIKE ?";
            $params[] = "%{$criterios['nombre']}%";
            $types .= "s";
        }
        
        $sql .= " GROUP BY c.ID_Comercio ORDER BY c.Nombre ASC";
        
        $stmt = $conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        
        $comercios = [];
        while ($row = $result->fetch_assoc()) {
            $comercios[] = Comercio::fromArray($row);
        }
        $stmt->close();
        
        return $comercios;
    }

    /**
     * {@inheritdoc}
     */
    public function eliminar(string $id): void
    {
        $conn = $this->db->getConnection();
        
        try {
            $conn->begin_transaction();
            
            // Primero verificar si tiene máquinas asociadas
            if ($this->tieneMaquinas($id)) {
                throw new \RuntimeException('No se puede eliminar el comercio porque tiene máquinas asociadas');
            }
            
            $sql = "DELETE FROM Comercio WHERE ID_Comercio = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('s', $id);
            $stmt->execute();
            $stmt->close();
            
            $conn->commit();
        } catch (\Exception $e) {
            $conn->rollback();
            throw new \RuntimeException("Error al eliminar comercio: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function existePorNombre(string $nombre, ?string $excluirId = null): bool
    {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT COUNT(*) as total FROM Comercio WHERE Nombre = ?";
        $params = [$nombre];
        $types = "s";
        
        if ($excluirId !== null) {
            $sql .= " AND ID_Comercio != ?";
            $params[] = $excluirId;
            $types .= "s";
        }
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        return $row['total'] > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function tieneMaquinas(string $id): bool
    {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT COUNT(*) as total FROM MaquinaRecreativa WHERE ID_Comercio = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        return $row['total'] > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function contar(array $criterios = []): int
    {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT COUNT(*) as total FROM Comercio WHERE 1=1";
        $params = [];
        $types = "";
        
        if (!empty($criterios['tipo'])) {
            $sql .= " AND Tipo = ?";
            $params[] = $criterios['tipo'];
            $types .= "s";
        }
        
        if (!empty($criterios['nombre'])) {
            $sql .= " AND Nombre LIKE ?";
            $params[] = "%{$criterios['nombre']}%";
            $types .= "s";
        }
        
        $stmt = $conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        return (int)$row['total'];
    }

    /**
     * {@inheritdoc}
     */
    public function findAll(array $filtros = [], int $offset = 0, int $limit = 10, string $orderBy = 'nombre', string $direction = 'ASC'): array
    {
        $conn = $this->db->getConnection();
        
        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
        $orderByColumns = ['nombre', 'tipo', 'fecha_registro'];
        $orderBy = in_array($orderBy, $orderByColumns) ? $orderBy : 'nombre';
        
        $sql = "SELECT c.*, COUNT(m.ID_Maquina) as cantidad_maquinas 
                FROM Comercio c
                LEFT JOIN MaquinaRecreativa m ON c.ID_Comercio = m.ID_Comercio
                WHERE 1=1";
        
        $params = [];
        $types = "";
        
        if (!empty($filtros['tipo'])) {
            $sql .= " AND c.Tipo = ?";
            $params[] = $filtros['tipo'];
            $types .= "s";
        }
        
        if (!empty($filtros['nombre'])) {
            $sql .= " AND c.Nombre LIKE ?";
            $params[] = "%{$filtros['nombre']}%";
            $types .= "s";
        }
        
        $sql .= " GROUP BY c.ID_Comercio ORDER BY c.{$orderBy} {$direction} LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        $types .= "ii";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $comercios = [];
        while ($row = $result->fetch_assoc()) {
            $comercios[] = Comercio::fromArray($row);
        }
        $stmt->close();
        
        return $comercios;
    }

    /**
     * {@inheritdoc}
     */
    public function count(array $filtros = []): int
    {
        return $this->contar($filtros);
    }
}