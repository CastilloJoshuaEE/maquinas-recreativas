<?php
/**
 * infrastructure/repositories/MySQLAdministradorRepository.php
 *
 * Implementación MySQL del repositorio para operaciones administrativas sobre usuarios.
 *
 * @package maquinas_recreativas\Infrastructure\Repositories
 */

namespace maquinas_recreativas\Infrastructure\Repositories;

use maquinas_recreativas\Domain\Usuario\AdministradorRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Infrastructure\Database\Database;
use maquinas_recreativas\Infrastructure\Security\CifradoHelper;

class MySQLAdministradorRepository extends MySQLUsuarioRepository implements AdministradorRepository
{
    private Database $db;

    public function __construct(Database $db)
    {
        parent::__construct($db);
        $this->db = $db;
    }

    public function findAllWithFilters(array $filters = []): array
    {
        $conn = $this->db->getConnection();

        $sql = "SELECT u.*, t.Especialidad, t.Cantidad_Actividades 
                FROM usuario u 
                LEFT JOIN Tecnico t ON u.ID_Usuario = t.ID_Tecnico 
                WHERE 1=1";

        $params = [];
        $types = "";

        if (!empty($filters['tipo'])) {
            $sql .= " AND u.tipo = ?";
            $params[] = $filters['tipo'];
            $types .= "s";
        }

        if (!empty($filters['estado'])) {
            $sql .= " AND u.estado = ?";
            $params[] = $filters['estado'];
            $types .= "s";
        }

        if (!empty($filters['ci'])) {
            $ciEncriptada = CifradoHelper::encriptar($filters['ci']);
            $sql .= " AND u.ci = ?";
            $params[] = $ciEncriptada;
            $types .= "s";
        }

        $sql .= " ORDER BY u.nombre ASC";

        if (isset($filters['limit'])) {
            $sql .= " LIMIT ?";
            $params[] = (int)$filters['limit'];
            $types .= "i";
        }

        if (isset($filters['offset'])) {
            $sql .= " OFFSET ?";
            $params[] = (int)$filters['offset'];
            $types .= "i";
        }

        $stmt = $conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();

        $rows = [];
        while ($row = $result->fetch_assoc()) {
            if (!empty($row['email'])) {
                $row['email'] = CifradoHelper::desencriptar($row['email']);
            }
            if (!empty($row['ci'])) {
                $row['ci'] = CifradoHelper::desencriptar($row['ci']);
            }
            $rows[] = $row;
        }
        $stmt->close();

        return $rows;
    }

    public function getEstadisticas(): array
    {
        $conn = $this->db->getConnection();

        $result = $conn->query("SELECT COUNT(*) as total FROM usuario");
        $total = $result->fetch_assoc()['total'];

        $result = $conn->query("SELECT tipo, COUNT(*) as cantidad FROM usuario GROUP BY tipo");
        $porTipo = [];
        while ($row = $result->fetch_assoc()) {
            $porTipo[$row['tipo']] = (int)$row['cantidad'];
        }

        $result = $conn->query("SELECT estado, COUNT(*) as cantidad FROM usuario GROUP BY estado");
        $porEstado = [];
        while ($row = $result->fetch_assoc()) {
            $porEstado[$row['estado']] = (int)$row['cantidad'];
        }

        return [
            'total' => (int)$total,
            'por_tipo' => $porTipo,
            'por_estado' => $porEstado,
        ];
    }

    public function updateEstado(Uuid $usuarioId, string $nuevoEstado): bool
    {
        $conn = $this->db->getConnection();

        $sql = "UPDATE usuario SET estado = ? WHERE ID_Usuario = ?";
        $stmt = $conn->prepare($sql);
        $idValue = $usuarioId->value();
        $stmt->bind_param('ss', $nuevoEstado, $idValue);
        $result = $stmt->execute();
        $stmt->close();

        return $result;
    }

    public function buscarPorNombre(string $termino, int $limit = 10): array
    {
        $conn = $this->db->getConnection();
        $termino = "%{$termino}%";

        $sql = "SELECT u.*, t.Especialidad, t.Cantidad_Actividades 
                FROM usuario u 
                LEFT JOIN Tecnico t ON u.ID_Usuario = t.ID_Tecnico 
                WHERE u.nombre LIKE ? OR u.apellido LIKE ?
                ORDER BY u.nombre ASC
                LIMIT ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssi', $termino, $termino, $limit);
        $stmt->execute();
        $result = $stmt->get_result();

        $rows = [];
        while ($row = $result->fetch_assoc()) {
            if (!empty($row['email'])) {
                $row['email'] = CifradoHelper::desencriptar($row['email']);
            }
            if (!empty($row['ci'])) {
                $row['ci'] = CifradoHelper::desencriptar($row['ci']);
            }
            $rows[] = $row;
        }
        $stmt->close();

        return $rows;
    }
}