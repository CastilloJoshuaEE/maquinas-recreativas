<?php
/**
 * infrastructure/repositories/MySQLDistribucionRepository.php
 *
 * Implementación MySQL del repositorio de distribución.
 *
 * @package maquinas_recreativas\Infrastructure\Repositories
 */

namespace maquinas_recreativas\Infrastructure\Repositories;

use maquinas_recreativas\Domain\Distribucion\InformeDistribucion;
use maquinas_recreativas\Domain\Distribucion\DistribucionRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Infrastructure\Database\Database;

class MySQLDistribucionRepository implements DistribucionRepository
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function save(InformeDistribucion $informe): void
    {
        $conn = $this->db->getConnection();
        $data = $informe->toArray();

        $sql = "INSERT INTO informe_distribucion (
                    ID_Distribucion, ID_Maquina, ID_Usuario_Comprobador, ID_Comercio, fecha_alta, fecha_baja, estado
                ) VALUES (?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    estado = VALUES(estado),
                    fecha_baja = VALUES(fecha_baja)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            'sssssss',
            $data['ID_Distribucion'],
            $data['ID_Maquina'],
            $data['ID_Usuario_Comprobador'],
            $data['ID_Comercio'],
            $data['fecha_alta'],
            $data['fecha_baja'],
            $data['estado']
        );
        $stmt->execute();
        $stmt->close();
    }

    public function findById(Uuid $id): ?InformeDistribucion
    {
        $conn = $this->db->getConnection();
        $sql = "SELECT * FROM informe_distribucion WHERE ID_Distribucion = ?";
        $stmt = $conn->prepare($sql);
        $idValue = $id->value();
        $stmt->bind_param('s', $idValue);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();

        return $data ? InformeDistribucion::fromArray($data) : null;
    }

    public function findByMaquina(Uuid $idMaquina): ?InformeDistribucion
    {
        $conn = $this->db->getConnection();
        $sql = "SELECT * FROM informe_distribucion WHERE ID_Maquina = ?";
        $stmt = $conn->prepare($sql);
        $idValue = $idMaquina->value();
        $stmt->bind_param('s', $idValue);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();

        return $data ? InformeDistribucion::fromArray($data) : null;
    }

    public function findAll(array $filters = [], int $limit = 100, int $offset = 0): array
    {
        $conn = $this->db->getConnection();
        $sql = "SELECT 
                    id.*,
                    m.Nombre_Maquina,
                    CONCAT(u.nombre, ' ', u.apellido) as Nombre_Tecnico,
                    c.Nombre as Nombre_Comercio,
                    c.Direccion as Direccion_Comercio,
                    c.Telefono as Telefono_Comercio,
                    c.Tipo as Tipo_Comercio
                FROM informe_distribucion id
                INNER JOIN MaquinaRecreativa m ON id.ID_Maquina = m.ID_Maquina
                INNER JOIN usuario u ON id.ID_Usuario_Comprobador = u.ID_Usuario
                INNER JOIN Comercio c ON id.ID_Comercio = c.ID_Comercio
                WHERE 1=1";

        $params = [];
        $types = "";

        if (!empty($filters['estado'])) {
            $sql .= " AND id.estado = ?";
            $params[] = $filters['estado'];
            $types .= "s";
        }

        if (!empty($filters['ID_Comercio'])) {
            $sql .= " AND id.ID_Comercio = ?";
            $params[] = $filters['ID_Comercio'];
            $types .= "s";
        }

        if (!empty($filters['ID_Maquina'])) {
            $sql .= " AND id.ID_Maquina = ?";
            $params[] = $filters['ID_Maquina'];
            $types .= "s";
        }

        if (!empty($filters['fecha_inicio'])) {
            $sql .= " AND DATE(id.fecha_alta) >= ?";
            $params[] = $filters['fecha_inicio'];
            $types .= "s";
        }

        if (!empty($filters['fecha_fin'])) {
            $sql .= " AND DATE(id.fecha_alta) <= ?";
            $params[] = $filters['fecha_fin'];
            $types .= "s";
        }

        $sql .= " ORDER BY id.fecha_alta DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        $types .= "ii";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();

        $informes = [];
        while ($row = $result->fetch_assoc()) {
            $informes[] = $row;
        }
        $stmt->close();

        return $informes;
    }

    public function updateEstado(Uuid $idMaquina, string $estado): bool
    {
        $conn = $this->db->getConnection();

        $estadosPermitidos = ['Operativa', 'Retirada', 'No operativa', 'Distribuyendose'];
        if (!in_array($estado, $estadosPermitidos, true)) {
            return false;
        }

        $sql = "UPDATE informe_distribucion SET estado = ? WHERE ID_Maquina = ?";
        $stmt = $conn->prepare($sql);
        $idValue = $idMaquina->value();
        $stmt->bind_param('ss', $estado, $idValue);
        $result = $stmt->execute();
        $stmt->close();

        return $result;
    }
}