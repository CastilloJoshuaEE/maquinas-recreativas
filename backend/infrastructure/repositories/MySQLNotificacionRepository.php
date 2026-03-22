<?php
/**
 * infrastructure/repositories/MySQLNotificacionRepository.php
 *
 * Implementación MySQL del repositorio de notificaciones.
 *
 * @package Reconocimiento\Infrastructure\Repositories
 */

namespace Reconocimiento\Infrastructure\Repositories;

use Reconocimiento\Domain\Notificacion\NotificacionMaquina;
use Reconocimiento\Domain\Notificacion\NotificacionReporte;
use Reconocimiento\Domain\Notificacion\NotificacionRepository;
use Reconocimiento\Domain\Shared\ValueObjects\Uuid;
use Reconocimiento\Infrastructure\Database\Database;
use PDO;

/**
 * Class MySQLNotificacionRepository
 */
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
                ) VALUES (
                    :id, :idRemitente, :idDestinatario, :idMaquina,
                    :tipo, :mensaje, :fecha, :estado
                )";

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':id' => $data['ID_Notificacion'],
            ':idRemitente' => $data['ID_Remitente'],
            ':idDestinatario' => $data['ID_Destinatario'],
            ':idMaquina' => $data['ID_Maquina'],
            ':tipo' => $data['Tipo'],
            ':mensaje' => $data['Mensaje'],
            ':fecha' => $data['Fecha'],
            ':estado' => $data['Estado']
        ]);
    }

    public function saveReporte(NotificacionReporte $notificacion): void
    {
        $conn = $this->db->getConnection();
        $data = $notificacion->toArray();

        $sql = "INSERT INTO notificaciones (
                    ID_Notificaciones, ID_Reporte, ID_Usuario, mensaje, fecha_hora, leida
                ) VALUES (
                    :id, :idReporte, :idUsuario, :mensaje, :fechaHora, :leida
                )";

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':id' => $data['ID_Notificaciones'],
            ':idReporte' => $data['ID_Reporte'],
            ':idUsuario' => $data['ID_Usuario'],
            ':mensaje' => $data['mensaje'],
            ':fechaHora' => $data['fecha_hora'],
            ':leida' => $data['leida']
        ]);
    }

    public function findMaquinaById(Uuid $id): ?NotificacionMaquina
    {
        $conn = $this->db->getConnection();
        $sql = "SELECT * FROM NotificacionMaquinaRecreativa WHERE ID_Notificacion = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':id' => $id->value()]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data ? NotificacionMaquina::fromArray($data) : null;
    }

    public function findReporteById(Uuid $id): ?NotificacionReporte
    {
        $conn = $this->db->getConnection();
        $sql = "SELECT * FROM notificaciones WHERE ID_Notificaciones = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':id' => $id->value()]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

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
                WHERE n.ID_Destinatario = :idDestinatario
                ORDER BY n.Fecha DESC";

        $stmt = $conn->prepare($sql);
        $stmt->execute([':idDestinatario' => $idDestinatario->value()]);

        $notificaciones = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $notificaciones[] = $row;
        }
        return $notificaciones;
    }

    public function findReportesByUsuario(Uuid $idUsuario): array
    {
        $conn = $this->db->getConnection();
        $sql = "SELECT n.*, r.descripcion as reporte_descripcion
                FROM notificaciones n
                LEFT JOIN reporte r ON n.ID_Reporte = r.ID_Reporte
                WHERE n.ID_Usuario = :idUsuario
                ORDER BY n.fecha_hora DESC";

        $stmt = $conn->prepare($sql);
        $stmt->execute([':idUsuario' => $idUsuario->value()]);

        $notificaciones = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $notificaciones[] = $row;
        }
        return $notificaciones;
    }

    public function findNoLeidasMaquina(Uuid $idDestinatario): int
    {
        $conn = $this->db->getConnection();
        $sql = "SELECT COUNT(*) as total FROM NotificacionMaquinaRecreativa 
                WHERE ID_Destinatario = :idDestinatario AND Estado = 'No leido'";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':idDestinatario' => $idDestinatario->value()]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return (int)$row['total'];
    }

    public function findNoLeidasReporte(Uuid $idUsuario): int
    {
        $conn = $this->db->getConnection();
        $sql = "SELECT COUNT(*) as cantidad FROM notificaciones 
                WHERE ID_Usuario = :idUsuario AND leida = 0";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':idUsuario' => $idUsuario->value()]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return (int)$row['cantidad'];
    }

    public function marcarLeidaMaquina(Uuid $id): bool
    {
        $conn = $this->db->getConnection();
        $sql = "UPDATE NotificacionMaquinaRecreativa SET Estado = 'Leido' WHERE ID_Notificacion = :id";
        $stmt = $conn->prepare($sql);
        return $stmt->execute([':id' => $id->value()]);
    }

    public function marcarLeidaReporte(Uuid $id, Uuid $idUsuario): bool
    {
        $conn = $this->db->getConnection();

        // Verificar que la notificación pertenece al usuario
        $checkSql = "SELECT ID_Notificaciones, leida FROM notificaciones 
                     WHERE ID_Notificaciones = :id AND ID_Usuario = :idUsuario";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->execute([
            ':id' => $id->value(),
            ':idUsuario' => $idUsuario->value()
        ]);

        if ($checkStmt->rowCount() === 0) {
            return false;
        }

        $row = $checkStmt->fetch(PDO::FETCH_ASSOC);
        if ($row['leida'] == 1) {
            return true; // Ya estaba leída
        }

        $sql = "UPDATE notificaciones SET leida = 1 
                WHERE ID_Notificaciones = :id AND ID_Usuario = :idUsuario";
        $stmt = $conn->prepare($sql);
        return $stmt->execute([
            ':id' => $id->value(),
            ':idUsuario' => $idUsuario->value()
        ]);
    }

    public function marcarTodasLeidasReporte(Uuid $idUsuario): bool
    {
        $conn = $this->db->getConnection();
        $sql = "UPDATE notificaciones SET leida = 1 WHERE ID_Usuario = :idUsuario";
        $stmt = $conn->prepare($sql);
        return $stmt->execute([':idUsuario' => $idUsuario->value()]);
    }
}