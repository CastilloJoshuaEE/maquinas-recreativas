<?php
/**
 * infrastructure/repositories/MySQLComentarioRepository.php
 */

namespace maquinas_recreativas\Infrastructure\Repositories;

use maquinas_recreativas\Domain\Comentario\Comentario;
use maquinas_recreativas\Domain\Comentario\ComentarioRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Infrastructure\Database\Database;
use maquinas_recreativas\Infrastructure\Security\CifradoHelper;

class MySQLComentarioRepository implements ComentarioRepository
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function save(Comentario $comentario): void
    {
        $conn = $this->db->getConnection();
        $data = $comentario->toArray();

        $sql = "INSERT INTO comentario (
                    ID_Comentario, ID_Reporte, ID_Usuario_Emisor, comentario, fecha_hora
                ) VALUES (
                    ?, ?, ?, ?, ?
                )";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            'sssss',
            $data['ID_Comentario'],
            $data['ID_Reporte'],
            $data['ID_Usuario_Emisor'],
            $data['comentario'],
            $data['fecha_hora']
        );
        $stmt->execute();
        $stmt->close();
    }

    public function findById(Uuid $id): ?Comentario
    {
        $conn = $this->db->getConnection();
        $sql = "SELECT * FROM comentario WHERE ID_Comentario = ?";
        $stmt = $conn->prepare($sql);
        $idValue = $id->value();
        $stmt->bind_param('s', $idValue);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();

        if ($data === null) {
            return null;
        }

        return Comentario::fromArray($data);
    }

    public function findByReporte(Uuid $idReporte, Uuid $idUsuario): array
    {
        $conn = $this->db->getConnection();
        $sql = "SELECT c.*, u.nombre, u.apellido, u.email, u.tipo,
                       CASE WHEN u.ID_Usuario = ? THEN 1 ELSE 0 END as es_propio
                FROM comentario c
                JOIN usuario u ON c.ID_Usuario_Emisor = u.ID_Usuario
                WHERE c.ID_Reporte = ?
                ORDER BY c.fecha_hora ASC";

        $stmt = $conn->prepare($sql);
        $idReporteValue = $idReporte->value();
        $idUsuarioValue = $idUsuario->value();
        $stmt->bind_param('ss', $idUsuarioValue, $idReporteValue);
        $stmt->execute();
        $result = $stmt->get_result();

        $comentarios = [];
        while ($row = $result->fetch_assoc()) {
            if (isset($row['email'])) {
                $row['email'] = CifradoHelper::desencriptar($row['email']);
            }
            $comentarios[] = $row;
        }
        $stmt->close();
        
        return $comentarios;
    }

    public function findByChat(Uuid $emisorId, Uuid $destinatarioId): array
    {
        $conn = $this->db->getConnection();
        $sql = "SELECT c.*, u.nombre, u.apellido, u.email, u.tipo,
                       r.ID_Usuario_Destinatario, r.ID_Usuario_Emisor
                FROM comentario c
                JOIN reporte r ON c.ID_Reporte = r.ID_Reporte
                JOIN usuario u ON c.ID_Usuario_Emisor = u.ID_Usuario
                WHERE (r.ID_Usuario_Emisor = ? AND r.ID_Usuario_Destinatario = ?)
                   OR (r.ID_Usuario_Emisor = ? AND r.ID_Usuario_Destinatario = ?)
                ORDER BY c.fecha_hora ASC";

        $stmt = $conn->prepare($sql);
        $emisorValue = $emisorId->value();
        $destinatarioValue = $destinatarioId->value();
        $stmt->bind_param('ssss', $emisorValue, $destinatarioValue, $destinatarioValue, $emisorValue);
        $stmt->execute();
        $result = $stmt->get_result();

        $comentarios = [];
        while ($row = $result->fetch_assoc()) {
            if (isset($row['email'])) {
                $row['email'] = CifradoHelper::desencriptar($row['email']);
            }
            $comentarios[] = $row;
        }
        $stmt->close();
        
        return $comentarios;
    }

    public function deleteByReporte(Uuid $idReporte): bool
    {
        $conn = $this->db->getConnection();
        $sql = "DELETE FROM comentario WHERE ID_Reporte = ?";
        $stmt = $conn->prepare($sql);
        $idReporteValue = $idReporte->value();
        $stmt->bind_param('s', $idReporteValue);
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }
}