<?php
/**
 * infrastructure/repositories/MySQLComponenteRepository.php
 */

namespace maquinas_recreativas\Infrastructure\Repositories;

use maquinas_recreativas\Domain\Componente\Componente;
use maquinas_recreativas\Domain\Componente\ComponenteRepository;
use maquinas_recreativas\Domain\Componente\TipoComponente;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Infrastructure\Database\Database;

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

        // Verificar si existe
        $checkSql = "SELECT COUNT(*) as total FROM componente WHERE ID_Componente = ?";
        $checkStmt = $conn->prepare($checkSql);
        $idValue = $data['ID_Componente'];
        $checkStmt->bind_param('s', $idValue);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        $exists = $checkResult->fetch_assoc()['total'] > 0;
        $checkStmt->close();

        if ($exists) {
            $sql = "UPDATE componente SET tipo = ?, nombre = ?, precio = ? WHERE ID_Componente = ?";
            $stmt = $conn->prepare($sql);
            $tipo = $data['tipo'];
            $nombre = $data['nombre'];
            $precio = $data['precio'];
            $stmt->bind_param('ssds', $tipo, $nombre, $precio, $idValue);
        } else {
            $sql = "INSERT INTO componente (ID_Componente, tipo, nombre, precio) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $tipo = $data['tipo'];
            $nombre = $data['nombre'];
            $precio = $data['precio'];
            $stmt->bind_param('sssd', $idValue, $tipo, $nombre, $precio);
        }
        $stmt->execute();
        $stmt->close();

        // Manejar asignación en componente_usuario
        $this->saveAsignacion($componente);
    }

    private function saveAsignacion(Componente $componente): void
    {
        $conn = $this->db->getConnection();
        $idComponente = $componente->id()->value();

        if ($componente->estaAsignado() && $componente->fechaAsignacion() !== null) {
            // Verificar si ya existe asignación activa
            $checkSql = "SELECT ID_Registro FROM componente_usuario 
                         WHERE ID_Componente = ? AND fecha_liberacion IS NULL";
            $checkStmt = $conn->prepare($checkSql);
            $checkStmt->bind_param('s', $idComponente);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            $existsActive = $checkResult->num_rows > 0;
            $checkStmt->close();

            if (!$existsActive) {
                $sql = "INSERT INTO componente_usuario (ID_Registro, ID_Componente, ID_Usuario, ID_Maquina, fecha_asignacion) 
                        VALUES (UUID(), ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $idUsuario = $componente->usuarioAsignado()?->value();
                $idMaquina = $componente->maquinaAsignada()?->value();
                $fechaAsignacion = $componente->fechaAsignacion()->format('Y-m-d H:i:s');
                $stmt->bind_param('ssss', $idComponente, $idUsuario, $idMaquina, $fechaAsignacion);
                $stmt->execute();
                $stmt->close();
            }
        } elseif ($componente->fechaLiberacion() !== null) {
            // Marcar como liberado
            $sql = "UPDATE componente_usuario 
                    SET fecha_liberacion = ? 
                    WHERE ID_Componente = ? AND fecha_liberacion IS NULL";
            $stmt = $conn->prepare($sql);
            $fechaLiberacion = $componente->fechaLiberacion()->format('Y-m-d H:i:s');
            $stmt->bind_param('ss', $fechaLiberacion, $idComponente);
            $stmt->execute();
            $stmt->close();
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
                WHERE c.ID_Componente = ?";
        
        $stmt = $conn->prepare($sql);
        $idValue = $id->value();
        $stmt->bind_param('s', $idValue);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();

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
        $types = "";
        
        if ($tipo !== null) {
            $sql .= " AND c.tipo = ?";
            $params[] = $tipo->value();
            $types .= "s";
        }
        
        $sql .= " GROUP BY c.ID_Componente LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        $types .= "ii";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $componentes = [];
        while ($row = $result->fetch_assoc()) {
            $componentes[] = Componente::fromArray($row);
        }
        $stmt->close();
        
        return $componentes;
    }

    public function findDisponibles(?TipoComponente $tipo = null): array
    {
        $conn = $this->db->getConnection();
        $sql = "SELECT c.* FROM componente c
                LEFT JOIN componente_usuario cu ON c.ID_Componente = cu.ID_Componente AND cu.fecha_liberacion IS NULL
                WHERE cu.ID_Componente IS NULL";
        
        $params = [];
        $types = "";
        
        if ($tipo !== null) {
            $sql .= " AND c.tipo = ?";
            $params[] = $tipo->value();
            $types .= "s";
        }
        
        $sql .= " ORDER BY c.nombre ASC";
        
        $stmt = $conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        
        $componentes = [];
        while ($row = $result->fetch_assoc()) {
            $componentes[] = Componente::fromArray($row);
        }
        $stmt->close();
        
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
                WHERE cu.ID_Usuario = ? AND cu.fecha_liberacion IS NULL";
        
        $params = [$idUsuario->value()];
        $types = "s";
        
        if ($idMaquina !== null) {
            $sql .= " AND cu.ID_Maquina = ?";
            $params[] = $idMaquina->value();
            $types .= "s";
        }
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $componentes = [];
        while ($row = $result->fetch_assoc()) {
            $componentes[] = Componente::fromArray($row);
        }
        $stmt->close();
        
        return $componentes;
    }

    public function countByTipo(?TipoComponente $tipo = null): int
    {
        $conn = $this->db->getConnection();
        $sql = "SELECT COUNT(*) as total FROM componente";
        
        if ($tipo !== null) {
            $sql .= " WHERE tipo = ?";
            $stmt = $conn->prepare($sql);
            $tipoValue = $tipo->value();
            $stmt->bind_param('s', $tipoValue);
        } else {
            $stmt = $conn->prepare($sql);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        return (int)$row['total'];
    }

    public function generarNumeroPlaca(): string
    {
        $conn = $this->db->getConnection();
        $anio = date('y');
        $prefijo = "PL{$anio}";
        $like = $prefijo . '%';
        
        $sql = "SELECT MAX(CAST(SUBSTRING(nombre, 5) AS UNSIGNED)) as max_seq
                FROM componente
                WHERE nombre LIKE ? AND tipo = 'Logistico'";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $like);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        $secuencia = ($row['max_seq'] ?? 0) + 1;
        return $prefijo . str_pad($secuencia, 3, '0', STR_PAD_LEFT);
    }
}