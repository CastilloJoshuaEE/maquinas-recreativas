<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helper/CifradoHelper.php';

class ComentarioModel {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    private function generateUUID($conn) {
        $sql = "SELECT UUID() as uuid";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        return $row['uuid'];
    }

    public function crearComentario($reporteId, $emisorId, $comentario) {
        $conn = $this->db->getConnection();
        
        try {
            $idComentario = $this->generateUUID($conn);
            
            // CORRECCIÓN: Usar los nombres correctos de columnas según bootstrap.php
            $sql = "INSERT INTO comentario (ID_Comentario, ID_Reporte, ID_Usuario_Emisor, comentario, fecha_hora) 
                    VALUES (?, ?, ?, ?, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssss", $idComentario, $reporteId, $emisorId, $comentario);
            
            if ($stmt->execute()) {
                return $idComentario;
            }
            
            return false;

        } catch (Exception $e) {
            error_log("Error en crearComentario: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerComentariosPorReporte($reporteId, $userId) {
        $conn = $this->db->getConnection();
        
        // CORRECCIÓN: Usar los nombres correctos de columnas
        $sql = "SELECT c.*, u.nombre, u.apellido, u.email, u.tipo,
                       CASE WHEN u.ID_Usuario = ? THEN 1 ELSE 0 END as es_propio
                FROM comentario c
                JOIN usuario u ON c.ID_Usuario_Emisor = u.ID_Usuario
                WHERE c.ID_Reporte = ?
                ORDER BY c.fecha_hora ASC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $userId, $reporteId);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $comentarios = [];
        
        while ($row = $result->fetch_assoc()) {
            if (isset($row['email'])) {
                $row['email'] = CifradoHelper::desencriptar($row['email']);
            }
            $comentarios[] = $row;
        }
        
        return $comentarios;
    }

    public function obtenerComentariosPorChat($emisorId, $destinatarioId) {
        $conn = $this->db->getConnection();
        
        // CORRECCIÓN: Usar los nombres correctos de columnas
        $sql = "SELECT c.*, u.nombre, u.apellido, u.email, u.tipo,
                       r.ID_Usuario_Destinatario, r.ID_Usuario_Emisor
                FROM comentario c
                JOIN reporte r ON c.ID_Reporte = r.ID_Reporte
                JOIN usuario u ON c.ID_Usuario_Emisor = u.ID_Usuario
                WHERE (r.ID_Usuario_Emisor = ? AND r.ID_Usuario_Destinatario = ?)
                   OR (r.ID_Usuario_Emisor = ? AND r.ID_Usuario_Destinatario = ?)
                ORDER BY c.fecha_hora ASC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $emisorId, $destinatarioId, $destinatarioId, $emisorId);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $comentarios = [];
        
        while ($row = $result->fetch_assoc()) {
            if (isset($row['email'])) {
                $row['email'] = CifradoHelper::desencriptar($row['email']);
            }
            $comentarios[] = $row;
        }
        
        return $comentarios;
    }
}
?>