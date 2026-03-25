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
use PDO;

/**
 * Class MySQLHistorialRepository
 */
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
                ) VALUES (
                    :idMaquina, :idUsuario, :tipoUsuario, :accion, :descripcion,
                    :estadoAnterior, :estadoNuevo, :etapaAnterior, :etapaNueva,
                    :ipAddress, :detalles, :fechaHora
                )";

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':idMaquina' => $data['ID_Maquina'],
            ':idUsuario' => $data['ID_Usuario'],
            ':tipoUsuario' => $data['tipo_usuario'],
            ':accion' => $data['accion'],
            ':descripcion' => $data['descripcion'],
            ':estadoAnterior' => $data['estado_anterior'],
            ':estadoNuevo' => $data['estado_nuevo'],
            ':etapaAnterior' => $data['etapa_anterior'],
            ':etapaNueva' => $data['etapa_nueva'],
            ':ipAddress' => $data['ip_address'],
            ':detalles' => $data['detalles_adicionales'],
            ':fechaHora' => $data['fecha_hora']
        ]);
    }

    public function saveActividad(HistorialActividad $actividad): void
    {
        $conn = $this->db->getConnection();
        $data = $actividad->toArray();

        $sql = "INSERT INTO historial_actividades (ID_Usuario, descripcion, fecha_registro) 
                VALUES (:idUsuario, :descripcion, :fechaRegistro)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':idUsuario' => $data['ID_Usuario'],
            ':descripcion' => $data['descripcion'],
            ':fechaRegistro' => $data['fecha_registro']
        ]);
    }

    public function findByMaquina(Uuid $idMaquina, int $limit = 50, int $offset = 0): array
    {
        $conn = $this->db->getConnection();
        $sql = "SELECT h.*, u.nombre as usuario_nombre, u.apellido as usuario_apellido, u.tipo as usuario_tipo,
                       m.Nombre_Maquina
                FROM historial_maquinas h
                INNER JOIN usuario u ON h.ID_Usuario = u.ID_Usuario
                INNER JOIN MaquinaRecreativa m ON h.ID_Maquina = m.ID_Maquina
                WHERE h.ID_Maquina = :idMaquina
                ORDER BY h.fecha_hora DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':idMaquina', $idMaquina->value());
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $historial = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $historial[] = HistorialMaquina::fromArray($row);
        }
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
                WHERE h.ID_Usuario = :idUsuario
                ORDER BY h.fecha_hora DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':idUsuario', $idUsuario->value());
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $historial = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $historial[] = HistorialMaquina::fromArray($row);
        }
        return $historial;
    }

    public function findByAccion(string $accion, int $limit = 100, int $offset = 0): array
    {
        $conn = $this->db->getConnection();
        $sql = "SELECT * FROM historial_maquinas 
                WHERE accion LIKE :accion
                ORDER BY fecha_hora DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':accion', "%{$accion}%");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $historial = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $historial[] = HistorialMaquina::fromArray($row);
        }
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

        if ($idMaquina !== null) {
            $sql .= " AND h.ID_Maquina = :idMaquina";
            $params[':idMaquina'] = $idMaquina->value();
        }

        if ($idUsuario !== null) {
            $sql .= " AND h.ID_Usuario = :idUsuario";
            $params[':idUsuario'] = $idUsuario->value();
        }

        if ($tipoUsuario !== null) {
            $sql .= " AND h.tipo_usuario = :tipoUsuario";
            $params[':tipoUsuario'] = $tipoUsuario;
        }

        if ($accion !== null) {
            $sql .= " AND h.accion LIKE :accion";
            $params[':accion'] = "%{$accion}%";
        }

        if ($fechaInicio !== null) {
            $sql .= " AND DATE(h.fecha_hora) >= :fechaInicio";
            $params[':fechaInicio'] = $fechaInicio;
        }

        if ($fechaFin !== null) {
            $sql .= " AND DATE(h.fecha_hora) <= :fechaFin";
            $params[':fechaFin'] = $fechaFin;
        }

        $sql .= " ORDER BY h.fecha_hora DESC LIMIT :limit OFFSET :offset";
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

        $historial = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $historial[] = HistorialMaquina::fromArray($row);
        }
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

        if ($idMaquina !== null) {
            $sql .= " AND h.ID_Maquina = :idMaquina";
            $params[':idMaquina'] = $idMaquina->value();
        }

        if ($idUsuario !== null) {
            $sql .= " AND h.ID_Usuario = :idUsuario";
            $params[':idUsuario'] = $idUsuario->value();
        }

        if ($tipoUsuario !== null) {
            $sql .= " AND h.tipo_usuario = :tipoUsuario";
            $params[':tipoUsuario'] = $tipoUsuario;
        }

        if ($accion !== null) {
            $sql .= " AND h.accion LIKE :accion";
            $params[':accion'] = "%{$accion}%";
        }

        if ($fechaInicio !== null) {
            $sql .= " AND DATE(h.fecha_hora) >= :fechaInicio";
            $params[':fechaInicio'] = $fechaInicio;
        }

        if ($fechaFin !== null) {
            $sql .= " AND DATE(h.fecha_hora) <= :fechaFin";
            $params[':fechaFin'] = $fechaFin;
        }

        $stmt = $conn->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$row['total'];
    }

    public function findActividadesByUsuario(Uuid $idUsuario, int $limit = 50): array
    {
        $conn = $this->db->getConnection();
        $sql = "SELECT * FROM historial_actividades 
                WHERE ID_Usuario = :idUsuario 
                ORDER BY fecha_registro DESC 
                LIMIT :limit";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':idUsuario', $idUsuario->value());
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $actividades = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $actividades[] = HistorialActividad::fromArray($row);
        }
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