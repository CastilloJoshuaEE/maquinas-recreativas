<?php
/**
 * infrastructure/repositories/MySQLNotificacionRepository.php
 *
 * Implementación MySQL del repositorio de notificaciones.
 *
 * @package maquinas_recreativas\Infrastructure\Repositories
 */

namespace maquinas_recreativas\Infrastructure\Repositories;

use maquinas_recreativas\Domain\Notificacion\NotificacionMaquina;
use maquinas_recreativas\Domain\Notificacion\NotificacionReporte;
use maquinas_recreativas\Domain\Notificacion\NotificacionRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Infrastructure\Database\Database;

class MySQLNotificacionRepository implements NotificacionRepository
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function saveMaquina(NotificacionMaquina $notificacion): void
    {
        $conn = $this->db->getConnection();
        $data = $notificacion->toArray();

        $sql = "INSERT INTO NotificacionMaquinaRecreativa (
                    ID_Notificacion, ID_Remitente, ID_Destinatario, ID_Maquina,
                    Tipo, Mensaje, Fecha, Estado
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            'ssssssss',
            $data['ID_Notificacion'],
            $data['ID_Remitente'],
            $data['ID_Destinatario'],
            $data['ID_Maquina'],
            $data['Tipo'],
            $data['Mensaje'],
            $data['Fecha'],
            $data['Estado']
        );
        $stmt->execute();
        $stmt->close();
    }

    public function saveReporte(NotificacionReporte $notificacion): void
    {
        $conn = $this->db->getConnection();
        $data = $notificacion->toArray();

        $sql = "INSERT INTO notificaciones (
                    ID_Notificaciones, ID_Reporte, ID_Usuario, mensaje, fecha_hora, leida
                ) VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            'sssssi',
            $data['ID_Notificaciones'],
            $data['ID_Reporte'],
            $data['ID_Usuario'],
            $data['mensaje'],
            $data['fecha_hora'],
            $data['leida']
        );
        $stmt->execute();
        $stmt->close();
    }

    public function findMaquinaById(Uuid $id): ?NotificacionMaquina
    {
        $conn = $this->db->getConnection();
        $sql = "SELECT * FROM NotificacionMaquinaRecreativa WHERE ID_Notificacion = ?";
        $stmt = $conn->prepare($sql);
        $idValue = $id->value();
        $stmt->bind_param('s', $idValue);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();

        return $data ? NotificacionMaquina::fromArray($data) : null;
    }

    public function findReporteById(Uuid $id): ?NotificacionReporte
    {
        $conn = $this->db->getConnection();
        $sql = "SELECT * FROM notificaciones WHERE ID_Notificaciones = ?";
        $stmt = $conn->prepare($sql);
        $idValue = $id->value();
        $stmt->bind_param('s', $idValue);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();

        return $data ? NotificacionReporte::fromArray($data) : null;
    }

    public function findMaquinasByDestinatario(Uuid $idDestinatario): array
    {
        $conn = $this->db->getConnection();
        $sql = "SELECT n.*, 
                       u.nombre as nombre_remitente, 
                       u.apellido as apellido_remitente,
                       m.Nombre_Maquina,
                       c.Nombre as NombreComercio,
                       c.Direccion as DireccionComercio
                FROM NotificacionMaquinaRecreativa n
                LEFT JOIN usuario u ON n.ID_Remitente = u.ID_Usuario
                LEFT JOIN MaquinaRecreativa m ON n.ID_Maquina = m.ID_Maquina
                LEFT JOIN Comercio c ON m.ID_Comercio = c.ID_Comercio
                WHERE n.ID_Destinatario = ?
                ORDER BY n.Fecha DESC";

        $stmt = $conn->prepare($sql);
        $idValue = $idDestinatario->value();
        $stmt->bind_param('s', $idValue);
        $stmt->execute();
        $result = $stmt->get_result();

        $notificaciones = [];
        while ($row = $result->fetch_assoc()) {
            $notificaciones[] = $row;
        }
        $stmt->close();

        return $notificaciones;
    }

    public function findReportesByUsuario(Uuid $idUsuario): array
    {
        $conn = $this->db->getConnection();
        $sql = "SELECT n.*, r.descripcion as reporte_descripcion
                FROM notificaciones n
                LEFT JOIN reporte r ON n.ID_Reporte = r.ID_Reporte
                WHERE n.ID_Usuario = ?
                ORDER BY n.fecha_hora DESC";

        $stmt = $conn->prepare($sql);
        $idValue = $idUsuario->value();
        $stmt->bind_param('s', $idValue);
        $stmt->execute();
        $result = $stmt->get_result();

        $notificaciones = [];
        while ($row = $result->fetch_assoc()) {
            $notificaciones[] = $row;
        }
        $stmt->close();

        return $notificaciones;
    }

    public function findNoLeidasMaquina(Uuid $idDestinatario): int
    {
        $conn = $this->db->getConnection();
        $sql = "SELECT COUNT(*) as total FROM NotificacionMaquinaRecreativa 
                WHERE ID_Destinatario = ? AND Estado = 'No leido'";
        $stmt = $conn->prepare($sql);
        $idValue = $idDestinatario->value();
        $stmt->bind_param('s', $idValue);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        return (int)$row['total'];
    }

    public function findNoLeidasReporte(Uuid $idUsuario): int
    {
        $conn = $this->db->getConnection();
        $sql = "SELECT COUNT(*) as cantidad FROM notificaciones 
                WHERE ID_Usuario = ? AND leida = 0";
        $stmt = $conn->prepare($sql);
        $idValue = $idUsuario->value();
        $stmt->bind_param('s', $idValue);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        return (int)$row['cantidad'];
    }

    public function marcarLeidaMaquina(Uuid $id): bool
    {
        $conn = $this->db->getConnection();
        $sql = "UPDATE NotificacionMaquinaRecreativa SET Estado = 'Leido' WHERE ID_Notificacion = ?";
        $stmt = $conn->prepare($sql);
        $idValue = $id->value();
        $stmt->bind_param('s', $idValue);
        $result = $stmt->execute();
        $stmt->close();

        return $result;
    }

    public function marcarLeidaReporte(Uuid $id, Uuid $idUsuario): bool
    {
        $conn = $this->db->getConnection();

        $checkSql = "SELECT ID_Notificaciones, leida FROM notificaciones 
                     WHERE ID_Notificaciones = ? AND ID_Usuario = ?";
        $checkStmt = $conn->prepare($checkSql);
        $idValue = $id->value();
        $idUsuarioValue = $idUsuario->value();
        $checkStmt->bind_param('ss', $idValue, $idUsuarioValue);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        
        if ($result->num_rows === 0) {
            $checkStmt->close();
            return false;
        }

        $row = $result->fetch_assoc();
        $checkStmt->close();

        if ($row['leida'] == 1) {
            return true;
        }

        $sql = "UPDATE notificaciones SET leida = 1 
                WHERE ID_Notificaciones = ? AND ID_Usuario = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ss', $idValue, $idUsuarioValue);
        $result = $stmt->execute();
        $stmt->close();

        return $result;
    }

    public function marcarTodasLeidasReporte(Uuid $idUsuario): bool
    {
        $conn = $this->db->getConnection();
        $sql = "UPDATE notificaciones SET leida = 1 WHERE ID_Usuario = ?";
        $stmt = $conn->prepare($sql);
        $idValue = $idUsuario->value();
        $stmt->bind_param('s', $idValue);
        $result = $stmt->execute();
        $stmt->close();

        return $result;
    }
}