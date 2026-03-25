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
use PDO;

/**
 * Class MySQLDistribucionRepository
 */
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
                    ID_Maquina, ID_Usuario_Comprobador, ID_Comercio, fecha_alta, estado
                ) VALUES (
                    :idMaquina, :idUsuario, :idComercio, :fechaAlta, :estado
                ) ON DUPLICATE KEY UPDATE
                    estado = VALUES(estado),
                    fecha_baja = :fechaBaja";

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':idMaquina' => $data['ID_Maquina'],
            ':idUsuario' => $data['ID_Usuario_Comprobador'],
            ':idComercio' => $data['ID_Comercio'],
            ':fechaAlta' => $data['fecha_alta'],
            ':fechaBaja' => $data['fecha_baja'],
            ':estado' => $data['estado']
        ]);
    }

    public function findById(Uuid $id): ?InformeDistribucion
    {
        $conn = $this->db->getConnection();
        $sql = "SELECT * FROM informe_distribucion WHERE ID_Distribucion = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':id' => $id->value()]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data ? InformeDistribucion::fromArray($data) : null;
    }

    public function findByMaquina(Uuid $idMaquina): ?InformeDistribucion
    {
        $conn = $this->db->getConnection();
        $sql = "SELECT * FROM informe_distribucion WHERE ID_Maquina = :idMaquina";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':idMaquina' => $idMaquina->value()]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

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

        if (!empty($filters['estado'])) {
            $sql .= " AND id.estado = :estado";
            $params[':estado'] = $filters['estado'];
        }

        if (!empty($filters['ID_Comercio'])) {
            $sql .= " AND id.ID_Comercio = :idComercio";
            $params[':idComercio'] = $filters['ID_Comercio'];
        }

        if (!empty($filters['ID_Maquina'])) {
            $sql .= " AND id.ID_Maquina = :idMaquina";
            $params[':idMaquina'] = $filters['ID_Maquina'];
        }

        if (!empty($filters['fecha_inicio'])) {
            $sql .= " AND DATE(id.fecha_alta) >= :fechaInicio";
            $params[':fechaInicio'] = $filters['fecha_inicio'];
        }

        if (!empty($filters['fecha_fin'])) {
            $sql .= " AND DATE(id.fecha_alta) <= :fechaFin";
            $params[':fechaFin'] = $filters['fecha_fin'];
        }

        $sql .= " ORDER BY id.fecha_alta DESC LIMIT :limit OFFSET :offset";
        $params[':limit'] = $limit;
        $params[':offset'] = $offset;

        $stmt = $conn->prepare($sql);
        foreach ($params as $key => $value) {
            if (is_int($value)) {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $value);
            }
        }
        $stmt->execute();

        $informes = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $informes[] = $row;
        }
        return $informes;
    }

    public function updateEstado(Uuid $idMaquina, string $estado): bool
    {
        $conn = $this->db->getConnection();

        $estadosPermitidos = ['Operativa', 'Retirada', 'No operativa', 'Distribuyendose'];
        if (!in_array($estado, $estadosPermitidos, true)) {
            return false;
        }

        $sql = "UPDATE informe_distribucion SET estado = :estado WHERE ID_Maquina = :idMaquina";
        $stmt = $conn->prepare($sql);
        return $stmt->execute([
            ':estado' => $estado,
            ':idMaquina' => $idMaquina->value()
        ]);
    }
}