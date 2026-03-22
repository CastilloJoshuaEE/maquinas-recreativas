<?php
/**
 * infrastructure/repositories/MySQLComponenteRepository.php
 *
 * Implementación MySQL del repositorio de componentes.
 *
 * @package Reconocimiento\Infrastructure\Repositories
 */

namespace Reconocimiento\Infrastructure\Repositories;

use Reconocimiento\Domain\Componente\Componente;
use Reconocimiento\Domain\Componente\ComponenteRepository;
use Reconocimiento\Domain\Componente\TipoComponente;
use Reconocimiento\Domain\Shared\ValueObjects\Uuid;
use Reconocimiento\Infrastructure\Database\Database;
use PDO;

/**
 * Class MySQLComponenteRepository
 */
class MySQLComponenteRepository implements ComponenteRepository
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function save(Componente $componente): void
    {
        $conn = $this->db->getConnection();
        $data = $componente->toArray();

        $sql = "INSERT INTO componente (ID_Componente, tipo, nombre, precio) 
                VALUES (:id, :tipo, :nombre, :precio)
                ON DUPLICATE KEY UPDATE
                    tipo = VALUES(tipo),
                    nombre = VALUES(nombre),
                    precio = VALUES(precio)";

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':id' => $data['ID_Componente'],
            ':tipo' => $data['tipo'],
            ':nombre' => $data['nombre'],
            ':precio' => $data['precio']
        ]);

        // Manejar asignación/liberación en componente_usuario
        $this->saveAsignacion($componente);
    }

    private function saveAsignacion(Componente $componente): void
    {
        $conn = $this->db->getConnection();

        if ($componente->estaAsignado() && $componente->fechaAsignacion() !== null) {
            // Verificar si ya existe asignación activa
            $checkSql = "SELECT ID_Registro FROM componente_usuario 
                         WHERE ID_Componente = :id AND fecha_liberacion IS NULL";
            $checkStmt = $conn->prepare($checkSql);
            $checkStmt->execute([':id' => $componente->id()->value()]);

            if ($checkStmt->rowCount() === 0) {
                $sql = "INSERT INTO componente_usuario (ID_Registro, ID_Componente, ID_Usuario, ID_Maquina, fecha_asignacion) 
                        VALUES (UUID(), :idComponente, :idUsuario, :idMaquina, :fechaAsignacion)";
                $stmt = $conn->prepare($sql);
                $stmt->execute([
                    ':idComponente' => $componente->id()->value(),
                    ':idUsuario' => $componente->usuarioAsignado()?->value(),
                    ':idMaquina' => $componente->maquinaAsignada()?->value(),
                    ':fechaAsignacion' => $componente->fechaAsignacion()->format('Y-m-d H:i:s')
                ]);
            }
        } elseif ($componente->fechaLiberacion() !== null) {
            // Marcar como liberado
            $sql = "UPDATE componente_usuario 
                    SET fecha_liberacion = :fechaLiberacion 
                    WHERE ID_Componente = :idComponente AND fecha_liberacion IS NULL";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':idComponente' => $componente->id()->value(),
                ':fechaLiberacion' => $componente->fechaLiberacion()->format('Y-m-d H:i:s')
            ]);
        }
    }

    public function findById(Uuid $id): ?Componente
    {
        $conn = $this->db->getConnection();
        $sql = "SELECT c.*, 
                       cu.ID_Usuario as usuario_uso, 
                       cu.ID_Maquina as maquina_uso,
                       cu.fecha_asignacion,
                       cu.fecha_liberacion
                FROM componente c
                LEFT JOIN componente_usuario cu ON c.ID_Componente = cu.ID_Componente AND cu.fecha_liberacion IS NULL
                WHERE c.ID_Componente = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':id' => $id->value()]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data ? Componente::fromArray($data) : null;
    }

    public function findByTipo(?TipoComponente $tipo = null, int $limit = 10, int $offset = 0): array
    {
        $conn = $this->db->getConnection();
        $sql = "SELECT c.*, 
                       cu.ID_Usuario as usuario_uso, 
                       cu.ID_Maquina as maquina_uso,
                       cu.fecha_asignacion,
                       cu.fecha_liberacion
                FROM componente c
                LEFT JOIN componente_usuario cu ON c.ID_Componente = cu.ID_Componente AND cu.fecha_liberacion IS NULL
                WHERE 1=1";
        $params = [];

        if ($tipo !== null) {
            $sql .= " AND c.tipo = :tipo";
            $params[':tipo'] = $tipo->value();
        }

        $sql .= " GROUP BY c.ID_Componente LIMIT :limit OFFSET :offset";
        $params[':limit'] = $limit;
        $params[':offset'] = $offset;

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

        if ($tipo !== null) {
            $sql .= " AND c.tipo = :tipo";
            $params[':tipo'] = $tipo->value();
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
        $sql = "SELECT c.*, 
                       cu.fecha_asignacion,
                       cu.ID_Maquina as maquina_uso,
                       m.Nombre_Maquina
                FROM componente_usuario cu
                INNER JOIN componente c ON cu.ID_Componente = c.ID_Componente
                LEFT JOIN MaquinaRecreativa m ON cu.ID_Maquina = m.ID_Maquina
                WHERE cu.ID_Usuario = :idUsuario AND cu.fecha_liberacion IS NULL";
        $params = [':idUsuario' => $idUsuario->value()];

        if ($idMaquina !== null) {
            $sql .= " AND cu.ID_Maquina = :idMaquina";
            $params[':idMaquina'] = $idMaquina->value();
        }

        $stmt = $conn->prepare($sql);
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
        $sql = "SELECT COUNT(*) as total FROM componente";
        $params = [];

        if ($tipo !== null) {
            $sql .= " WHERE tipo = :tipo";
            $params[':tipo'] = $tipo->value();
        }

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return (int)$result['total'];
    }

    public function generarNumeroPlaca(): string
    {
        $conn = $this->db->getConnection();
        $anio = date('y');
        $prefijo = "PL{$anio}";

        $sql = "SELECT MAX(CAST(SUBSTRING(nombre, 5) AS UNSIGNED)) as max_seq
                FROM componente
                WHERE nombre LIKE :like AND tipo = 'Logistico'";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':like' => $prefijo . '%']);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $secuencia = ($row['max_seq'] ?? 0) + 1;
        return $prefijo . str_pad($secuencia, 3, '0', STR_PAD_LEFT);
    }
}