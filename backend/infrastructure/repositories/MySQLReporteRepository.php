<?php
namespace maquinas_recreativas\Infrastructure\Repositories;

use maquinas_recreativas\Domain\Reporte\Reporte;
use maquinas_recreativas\Domain\Reporte\EstadoReporte;
use maquinas_recreativas\Domain\Reporte\ReporteRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Infrastructure\Database\Database;
use maquinas_recreativas\Infrastructure\Security\CifradoHelper;

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
                ) VALUES (?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    estado = VALUES(estado),
                    descripcion = VALUES(descripcion)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            'ssssss',
            $data['ID_Reporte'],
            $data['ID_Usuario_Emisor'],
            $data['ID_Usuario_Destinatario'],
            $data['descripcion'],
            $data['fecha_hora'],
            $data['estado']
        );
        $stmt->execute();
        $stmt->close();
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
                WHERE r.ID_Reporte = ?";

        $stmt = $conn->prepare($sql);
        $idValue = $id->value();
        $stmt->bind_param('s', $idValue);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();

        if (!$data) {
            return null;
        }

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
                WHERE r.ID_Usuario_Emisor = ? OR r.ID_Usuario_Destinatario = ?
                ORDER BY r.fecha_hora DESC";

        $stmt = $conn->prepare($sql);
        $idValue = $idUsuario->value();
        $stmt->bind_param('ss', $idValue, $idValue);
        $stmt->execute();
        $result = $stmt->get_result();

        $reportes = [];
        while ($row = $result->fetch_assoc()) {
            if (isset($row['emisor_email'])) {
                $row['emisor_email'] = CifradoHelper::desencriptar($row['emisor_email']);
            }
            if (isset($row['destinatario_email'])) {
                $row['destinatario_email'] = CifradoHelper::desencriptar($row['destinatario_email']);
            }
            $reportes[] = Reporte::fromArray($row);
        }
        $stmt->close();

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
                WHERE (r.ID_Usuario_Emisor = ? AND r.ID_Usuario_Destinatario = ?)
                   OR (r.ID_Usuario_Emisor = ? AND r.ID_Usuario_Destinatario = ?)
                ORDER BY r.fecha_hora ASC";

        $stmt = $conn->prepare($sql);
        $emisorValue = $emisorId->value();
        $destinatarioValue = $destinatarioId->value();
        $stmt->bind_param('ssss', $emisorValue, $destinatarioValue, $destinatarioValue, $emisorValue);
        $stmt->execute();
        $result = $stmt->get_result();

        $reportes = [];
        while ($row = $result->fetch_assoc()) {
            if (isset($row['emisor_email'])) {
                $row['emisor_email'] = CifradoHelper::desencriptar($row['emisor_email']);
            }
            if (isset($row['destinatario_email'])) {
                $row['destinatario_email'] = CifradoHelper::desencriptar($row['destinatario_email']);
            }
            $reportes[] = Reporte::fromArray($row);
        }
        $stmt->close();

        return $reportes;
    }

    public function findUsuariosChat(Uuid $idUsuario): array
    {
        $conn = $this->db->getConnection();
        $sql = "SELECT DISTINCT u.* FROM usuario u
                WHERE u.ID_Usuario IN (
                    SELECT DISTINCT ID_Usuario_Emisor FROM reporte WHERE ID_Usuario_Destinatario = ?
                    UNION
                    SELECT DISTINCT ID_Usuario_Destinatario FROM reporte WHERE ID_Usuario_Emisor = ?
                )
                AND u.ID_Usuario != ?
                ORDER BY u.nombre ASC";

        $stmt = $conn->prepare($sql);
        $idValue = $idUsuario->value();
        $stmt->bind_param('sss', $idValue, $idValue, $idValue);
        $stmt->execute();
        $result = $stmt->get_result();

        $usuarios = [];
        while ($row = $result->fetch_assoc()) {
            if (isset($row['email'])) {
                $row['email'] = CifradoHelper::desencriptar($row['email']);
            }
            $usuarios[] = $row;
        }
        $stmt->close();

        return $usuarios;
    }

    public function updateEstado(Uuid $id, EstadoReporte $estado): bool
    {
        $conn = $this->db->getConnection();
        $sql = "UPDATE reporte SET estado = ? WHERE ID_Reporte = ?";
        $stmt = $conn->prepare($sql);
        $estadoValue = $estado->value();
        $idValue = $id->value();
        $stmt->bind_param('ss', $estadoValue, $idValue);
        $result = $stmt->execute();
        $stmt->close();

        return $result;
    }
}