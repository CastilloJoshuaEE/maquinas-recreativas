<?php
/**
 * infrastructure/repositories/MySQLHistorialRepository.php
 *
 * Implementación MySQL del repositorio de historial.
 *
 * @package maquinas_recreativas\Infrastructure\Repositories
 */

namespace maquinas_recreativas\Infrastructure\Repositories;

use maquinas_recreativas\Domain\Historial\HistorialMaquina;
use maquinas_recreativas\Domain\Historial\HistorialActividad;
use maquinas_recreativas\Domain\Historial\HistorialRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Infrastructure\Database\Database;

class MySQLHistorialRepository implements HistorialRepository
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function save(HistorialMaquina $historial): void
    {
        $conn = $this->db->getConnection();
        $data = $historial->toArray();

        $sql = "INSERT INTO historial_maquinas (
                    ID_Maquina, ID_Usuario, tipo_usuario, accion, descripcion,
                    estado_anterior, estado_nuevo, etapa_anterior, etapa_nueva,
                    ip_address, detalles_adicionales, fecha_hora
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            'ssssssssssss',
            $data['ID_Maquina'],
            $data['ID_Usuario'],
            $data['tipo_usuario'],
            $data['accion'],
            $data['descripcion'],
            $data['estado_anterior'],
            $data['estado_nuevo'],
            $data['etapa_anterior'],
            $data['etapa_nueva'],
            $data['ip_address'],
            $data['detalles_adicionales'],
            $data['fecha_hora']
        );
        $stmt->execute();
        $stmt->close();
    }

    public function saveActividad(HistorialActividad $actividad): void
    {
        $conn = $this->db->getConnection();
        $data = $actividad->toArray();

        $sql = "INSERT INTO historial_actividades (ID_Usuario, descripcion, fecha_registro) 
                VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sss',
            $data['ID_Usuario'],
            $data['descripcion'],
            $data['fecha_registro']
        );
        $stmt->execute();
        $stmt->close();
    }

    public function findByMaquina(Uuid $idMaquina, int $limit = 50, int $offset = 0): array
    {
        $conn = $this->db->getConnection();
        $sql = "SELECT h.*, u.nombre as usuario_nombre, u.apellido as usuario_apellido, u.tipo as usuario_tipo,
                       m.Nombre_Maquina
                FROM historial_maquinas h
                INNER JOIN usuario u ON h.ID_Usuario = u.ID_Usuario
                INNER JOIN MaquinaRecreativa m ON h.ID_Maquina = m.ID_Maquina
                WHERE h.ID_Maquina = ?
                ORDER BY h.fecha_hora DESC
                LIMIT ? OFFSET ?";

        $stmt = $conn->prepare($sql);
        $idValue = $idMaquina->value();
        $stmt->bind_param('sii', $idValue, $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();

        $historial = [];
        while ($row = $result->fetch_assoc()) {
            $historial[] = HistorialMaquina::fromArray($row);
        }
        $stmt->close();

        return $historial;
    }

    public function findByUsuario(Uuid $idUsuario, int $limit = 50, int $offset = 0): array
    {
        $conn = $this->db->getConnection();
        $sql = "SELECT h.*, u.nombre as usuario_nombre, u.apellido as usuario_apellido,
                       m.Nombre_Maquina
                FROM historial_maquinas h
                INNER JOIN usuario u ON h.ID_Usuario = u.ID_Usuario
                INNER JOIN MaquinaRecreativa m ON h.ID_Maquina = m.ID_Maquina
                WHERE h.ID_Usuario = ?
                ORDER BY h.fecha_hora DESC
                LIMIT ? OFFSET ?";

        $stmt = $conn->prepare($sql);
        $idValue = $idUsuario->value();
        $stmt->bind_param('sii', $idValue, $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();

        $historial = [];
        while ($row = $result->fetch_assoc()) {
            $historial[] = HistorialMaquina::fromArray($row);
        }
        $stmt->close();

        return $historial;
    }

    public function findByAccion(string $accion, int $limit = 100, int $offset = 0): array
    {
        $conn = $this->db->getConnection();
        $sql = "SELECT * FROM historial_maquinas 
                WHERE accion LIKE ?
                ORDER BY fecha_hora DESC
                LIMIT ? OFFSET ?";

        $stmt = $conn->prepare($sql);
        $likeAccion = "%{$accion}%";
        $stmt->bind_param('sii', $likeAccion, $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();

        $historial = [];
        while ($row = $result->fetch_assoc()) {
            $historial[] = HistorialMaquina::fromArray($row);
        }
        $stmt->close();

        return $historial;
    }

    public function findGeneral(
        ?Uuid $idMaquina = null,
        ?Uuid $idUsuario = null,
        ?string $tipoUsuario = null,
        ?string $accion = null,
        ?string $fechaInicio = null,
        ?string $fechaFin = null,
        int $limit = 100,
        int $offset = 0
    ): array {
        $conn = $this->db->getConnection();
        $sql = "SELECT h.*, u.nombre as usuario_nombre, u.apellido as usuario_apellido, u.tipo as usuario_tipo,
                       m.Nombre_Maquina, c.Nombre as NombreComercio
                FROM historial_maquinas h
                INNER JOIN usuario u ON h.ID_Usuario = u.ID_Usuario
                INNER JOIN MaquinaRecreativa m ON h.ID_Maquina = m.ID_Maquina
                LEFT JOIN Comercio c ON m.ID_Comercio = c.ID_Comercio
                WHERE 1=1";

        $params = [];
        $types = "";

        if ($idMaquina !== null) {
            $sql .= " AND h.ID_Maquina = ?";
            $params[] = $idMaquina->value();
            $types .= "s";
        }

        if ($idUsuario !== null) {
            $sql .= " AND h.ID_Usuario = ?";
            $params[] = $idUsuario->value();
            $types .= "s";
        }

        if ($tipoUsuario !== null) {
            $sql .= " AND h.tipo_usuario = ?";
            $params[] = $tipoUsuario;
            $types .= "s";
        }

        if ($accion !== null) {
            $sql .= " AND h.accion LIKE ?";
            $params[] = "%{$accion}%";
            $types .= "s";
        }

        if ($fechaInicio !== null) {
            $sql .= " AND DATE(h.fecha_hora) >= ?";
            $params[] = $fechaInicio;
            $types .= "s";
        }

        if ($fechaFin !== null) {
            $sql .= " AND DATE(h.fecha_hora) <= ?";
            $params[] = $fechaFin;
            $types .= "s";
        }

        $sql .= " ORDER BY h.fecha_hora DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        $types .= "ii";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();

        $historial = [];
        while ($row = $result->fetch_assoc()) {
            $historial[] = HistorialMaquina::fromArray($row);
        }
        $stmt->close();

        return $historial;
    }

    public function countByFilters(
        ?Uuid $idMaquina = null,
        ?Uuid $idUsuario = null,
        ?string $tipoUsuario = null,
        ?string $accion = null,
        ?string $fechaInicio = null,
        ?string $fechaFin = null
    ): int {
        $conn = $this->db->getConnection();
        $sql = "SELECT COUNT(*) as total FROM historial_maquinas h WHERE 1=1";

        $params = [];
        $types = "";

        if ($idMaquina !== null) {
            $sql .= " AND h.ID_Maquina = ?";
            $params[] = $idMaquina->value();
            $types .= "s";
        }

        if ($idUsuario !== null) {
            $sql .= " AND h.ID_Usuario = ?";
            $params[] = $idUsuario->value();
            $types .= "s";
        }

        if ($tipoUsuario !== null) {
            $sql .= " AND h.tipo_usuario = ?";
            $params[] = $tipoUsuario;
            $types .= "s";
        }

        if ($accion !== null) {
            $sql .= " AND h.accion LIKE ?";
            $params[] = "%{$accion}%";
            $types .= "s";
        }

        if ($fechaInicio !== null) {
            $sql .= " AND DATE(h.fecha_hora) >= ?";
            $params[] = $fechaInicio;
            $types .= "s";
        }

        if ($fechaFin !== null) {
            $sql .= " AND DATE(h.fecha_hora) <= ?";
            $params[] = $fechaFin;
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

    public function findActividadesByUsuario(Uuid $idUsuario, int $limit = 50): array
    {
        $conn = $this->db->getConnection();
        $sql = "SELECT * FROM historial_actividades 
                WHERE ID_Usuario = ? 
                ORDER BY fecha_registro DESC 
                LIMIT ?";

        $stmt = $conn->prepare($sql);
        $idValue = $idUsuario->value();
        $stmt->bind_param('si', $idValue, $limit);
        $stmt->execute();
        $result = $stmt->get_result();

        $actividades = [];
        while ($row = $result->fetch_assoc()) {
            $actividades[] = HistorialActividad::fromArray($row);
        }
        $stmt->close();

        return $actividades;
    }

    public function getResumenReciente(int $limite = 20): array
    {
        $historial = $this->findGeneral(null, null, null, null, null, null, $limite, 0);

        $resumen = [
            'total' => count($historial),
            'por_accion' => [],
            'recientes' => []
        ];

        foreach ($historial as $item) {
            $accion = $item->accion();
            if (!isset($resumen['por_accion'][$accion])) {
                $resumen['por_accion'][$accion] = 0;
            }
            $resumen['por_accion'][$accion]++;

            if (count($resumen['recientes']) < 10) {
                $resumen['recientes'][] = [
                    'id' => $item->id()->value(),
                    'accion' => $accion,
                    'descripcion' => $item->descripcion(),
                    'fecha' => $item->fechaHora()->format('Y-m-d H:i:s'),
                    'maquina' => $item->idMaquina()->value()
                ];
            }
        }

        return $resumen;
    }
}