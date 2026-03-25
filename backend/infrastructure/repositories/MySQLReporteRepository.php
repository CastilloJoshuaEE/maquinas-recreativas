<?php
/**
 * infrastructure/repositories/MySQLReporteRepository.php
 *
 * Implementación MySQL del repositorio de reportes.
 *
 * @package maquinas_recreativas\Infrastructure\Repositories
 */

namespace maquinas_recreativas\Infrastructure\Repositories;

use maquinas_recreativas\Domain\Reporte\Reporte;
use maquinas_recreativas\Domain\Reporte\EstadoReporte;
use maquinas_recreativas\Domain\Reporte\ReporteRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Infrastructure\Database\Database;
use maquinas_recreativas\Infrastructure\Security\CifradoHelper;
use PDO;

/**
 * Class MySQLReporteRepository
 */
class MySQLReporteRepository implements ReporteRepository
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function save(Reporte $reporte): void
    {
        $conn = $this->db->getConnection();
        $data = $reporte->toArray();

        $sql = "INSERT INTO reporte (
                    ID_Reporte, ID_Usuario_Emisor, ID_Usuario_Destinatario,
                    descripcion, fecha_hora, estado
                ) VALUES (
                    :id, :idEmisor, :idDestinatario, :descripcion, :fechaHora, :estado
                ) ON DUPLICATE KEY UPDATE
                    estado = VALUES(estado),
                    descripcion = VALUES(descripcion)";

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':id' => $data['ID_Reporte'],
            ':idEmisor' => $data['ID_Usuario_Emisor'],
            ':idDestinatario' => $data['ID_Usuario_Destinatario'],
            ':descripcion' => $data['descripcion'],
            ':fechaHora' => $data['fecha_hora'],
            ':estado' => $data['estado']
        ]);
    }

    public function findById(Uuid $id): ?Reporte
    {
        $conn = $this->db->getConnection();
        $sql = "SELECT r.*, 
                       e.nombre as emisor_nombre, e.apellido as emisor_apellido, e.email as emisor_email,
                       d.nombre as destinatario_nombre, d.apellido as destinatario_apellido, d.email as destinatario_email
                FROM reporte r
                JOIN usuario e ON r.ID_Usuario_Emisor = e.ID_Usuario
                LEFT JOIN usuario d ON r.ID_Usuario_Destinatario = d.ID_Usuario
                WHERE r.ID_Reporte = :id";

        $stmt = $conn->prepare($sql);
        $stmt->execute([':id' => $id->value()]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        // Desencriptar emails
        if (isset($data['emisor_email'])) {
            $data['emisor_email'] = CifradoHelper::desencriptar($data['emisor_email']);
        }
        if (isset($data['destinatario_email'])) {
            $data['destinatario_email'] = CifradoHelper::desencriptar($data['destinatario_email']);
        }

        return Reporte::fromArray($data);
    }

    public function findByUsuario(Uuid $idUsuario): array
    {
        $conn = $this->db->getConnection();
        $sql = "SELECT r.*, 
                       e.nombre as emisor_nombre, e.apellido as emisor_apellido, e.email as emisor_email,
                       d.nombre as destinatario_nombre, d.apellido as destinatario_apellido, d.email as destinatario_email
                FROM reporte r
                JOIN usuario e ON r.ID_Usuario_Emisor = e.ID_Usuario
                LEFT JOIN usuario d ON r.ID_Usuario_Destinatario = d.ID_Usuario
                WHERE r.ID_Usuario_Emisor = :idUsuario OR r.ID_Usuario_Destinatario = :idUsuario
                ORDER BY r.fecha_hora DESC";

        $stmt = $conn->prepare($sql);
        $stmt->execute([':idUsuario' => $idUsuario->value()]);

        $reportes = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if (isset($row['emisor_email'])) {
                $row['emisor_email'] = CifradoHelper::desencriptar($row['emisor_email']);
            }
            if (isset($row['destinatario_email'])) {
                $row['destinatario_email'] = CifradoHelper::desencriptar($row['destinatario_email']);
            }
            $reportes[] = Reporte::fromArray($row);
        }
        return $reportes;
    }

    public function findChat(Uuid $emisorId, Uuid $destinatarioId): array
    {
        $conn = $this->db->getConnection();
        $sql = "SELECT r.*, 
                       e.nombre as emisor_nombre, e.apellido as emisor_apellido, e.email as emisor_email,
                       d.nombre as destinatario_nombre, d.apellido as destinatario_apellido, d.email as destinatario_email
                FROM reporte r
                JOIN usuario e ON r.ID_Usuario_Emisor = e.ID_Usuario
                LEFT JOIN usuario d ON r.ID_Usuario_Destinatario = d.ID_Usuario
                WHERE (r.ID_Usuario_Emisor = :emisorId AND r.ID_Usuario_Destinatario = :destinatarioId)
                   OR (r.ID_Usuario_Emisor = :destinatarioId AND r.ID_Usuario_Destinatario = :emisorId)
                ORDER BY r.fecha_hora ASC";

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':emisorId' => $emisorId->value(),
            ':destinatarioId' => $destinatarioId->value()
        ]);

        $reportes = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if (isset($row['emisor_email'])) {
                $row['emisor_email'] = CifradoHelper::desencriptar($row['emisor_email']);
            }
            if (isset($row['destinatario_email'])) {
                $row['destinatario_email'] = CifradoHelper::desencriptar($row['destinatario_email']);
            }
            $reportes[] = Reporte::fromArray($row);
        }
        return $reportes;
    }

    public function findUsuariosChat(Uuid $idUsuario): array
    {
        $conn = $this->db->getConnection();
        $sql = "SELECT DISTINCT u.* FROM usuario u
                WHERE u.ID_Usuario IN (
                    SELECT DISTINCT ID_Usuario_Emisor FROM reporte WHERE ID_Usuario_Destinatario = :idUsuario
                    UNION
                    SELECT DISTINCT ID_Usuario_Destinatario FROM reporte WHERE ID_Usuario_Emisor = :idUsuario
                )
                AND u.ID_Usuario != :idUsuario
                ORDER BY u.nombre ASC";

        $stmt = $conn->prepare($sql);
        $stmt->execute([':idUsuario' => $idUsuario->value()]);

        $usuarios = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if (isset($row['email'])) {
                $row['email'] = CifradoHelper::desencriptar($row['email']);
            }
            $usuarios[] = $row;
        }
        return $usuarios;
    }

    public function updateEstado(Uuid $id, EstadoReporte $estado): bool
    {
        $conn = $this->db->getConnection();
        $sql = "UPDATE reporte SET estado = :estado WHERE ID_Reporte = :id";
        $stmt = $conn->prepare($sql);
        return $stmt->execute([
            ':estado' => $estado->value(),
            ':id' => $id->value()
        ]);
    }
}