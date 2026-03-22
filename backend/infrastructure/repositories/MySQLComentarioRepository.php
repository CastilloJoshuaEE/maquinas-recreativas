<?php
/**
 * infrastructure/repositories/MySQLComentarioRepository.php
 *
 * Implementación MySQL del repositorio de comentarios.
 *
 * @package Reconocimiento\Infrastructure\Repositories
 */

namespace Reconocimiento\Infrastructure\Repositories;

use Reconocimiento\Domain\Comentario\Comentario;
use Reconocimiento\Domain\Comentario\ComentarioRepository;
use Reconocimiento\Domain\Shared\ValueObjects\Uuid;
use Reconocimiento\Infrastructure\Database\Database;
use Reconocimiento\Infrastructure\Security\CifradoHelper;
use PDO;

/**
 * Class MySQLComentarioRepository
 */
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
                    :id, :idReporte, :idUsuarioEmisor, :comentario, :fechaHora
                )";

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':id' => $data['ID_Comentario'],
            ':idReporte' => $data['ID_Reporte'],
            ':idUsuarioEmisor' => $data['ID_Usuario_Emisor'],
            ':comentario' => $data['comentario'],
            ':fechaHora' => $data['fecha_hora']
        ]);
    }

    public function findById(Uuid $id): ?Comentario
    {
        $conn = $this->db->getConnection();
        $sql = "SELECT * FROM comentario WHERE ID_Comentario = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':id' => $id->value()]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data ? Comentario::fromArray($data) : null;
    }

    public function findByReporte(Uuid $idReporte, Uuid $idUsuario): array
    {
        $conn = $this->db->getConnection();
        $sql = "SELECT c.*, u.nombre, u.apellido, u.email, u.tipo,
                       CASE WHEN u.ID_Usuario = :idUsuario THEN 1 ELSE 0 END as es_propio
                FROM comentario c
                JOIN usuario u ON c.ID_Usuario_Emisor = u.ID_Usuario
                WHERE c.ID_Reporte = :idReporte
                ORDER BY c.fecha_hora ASC";

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':idReporte' => $idReporte->value(),
            ':idUsuario' => $idUsuario->value()
        ]);

        $comentarios = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if (isset($row['email'])) {
                $row['email'] = CifradoHelper::desencriptar($row['email']);
            }
            $comentarios[] = $row;
        }
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
                WHERE (r.ID_Usuario_Emisor = :emisorId AND r.ID_Usuario_Destinatario = :destinatarioId)
                   OR (r.ID_Usuario_Emisor = :destinatarioId AND r.ID_Usuario_Destinatario = :emisorId)
                ORDER BY c.fecha_hora ASC";

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':emisorId' => $emisorId->value(),
            ':destinatarioId' => $destinatarioId->value()
        ]);

        $comentarios = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if (isset($row['email'])) {
                $row['email'] = CifradoHelper::desencriptar($row['email']);
            }
            $comentarios[] = $row;
        }
        return $comentarios;
    }

    public function deleteByReporte(Uuid $idReporte): bool
    {
        $conn = $this->db->getConnection();
        $sql = "DELETE FROM comentario WHERE ID_Reporte = :idReporte";
        $stmt = $conn->prepare($sql);
        return $stmt->execute([':idReporte' => $idReporte->value()]);
    }
}