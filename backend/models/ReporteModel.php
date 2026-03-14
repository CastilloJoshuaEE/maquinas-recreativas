<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helper/CifradoHelper.php';

class ReporteModel {
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

    public function crearReporte($emisorId, $destinatarioId, $descripcion) {
        $conn = $this->db->getConnection();
        
        try {
            // Verificar que los usuarios existen
            $checkEmisor = $conn->query("SELECT ID_Usuario FROM usuario WHERE ID_Usuario = '$emisorId'");
            if ($checkEmisor->num_rows == 0) {
                throw new Exception("El usuario emisor no existe");
            }
            
            if ($destinatarioId) {
                $checkDestinatario = $conn->query("SELECT ID_Usuario FROM usuario WHERE ID_Usuario = '$destinatarioId'");
                if ($checkDestinatario->num_rows == 0) {
                    throw new Exception("El usuario destinatario no existe");
                }
            }

            $idReporte = $this->generateUUID($conn);
            
            $sql = "INSERT INTO reporte (ID_Reporte, ID_Usuario_Emisor, ID_Usuario_Destinatario, descripcion, fecha_hora, estado) 
                    VALUES (?, ?, ?, ?, NOW(), 'Pendiente')";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssss", $idReporte, $emisorId, $destinatarioId, $descripcion);
            
            if ($stmt->execute()) {
                return $idReporte;
            }
            
            return false;

        } catch (Exception $e) {
            error_log("Error en crearReporte: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerReportesPorUsuario($userId) {
        $conn = $this->db->getConnection();
        
        // CORRECCIÓN: Usar los nombres correctos de columnas
        $sql = "SELECT r.*, 
                       e.nombre as emisor_nombre, e.apellido as emisor_apellido, e.email as emisor_email,
                       d.nombre as destinatario_nombre, d.apellido as destinatario_apellido, d.email as destinatario_email
                FROM reporte r
                JOIN usuario e ON r.ID_Usuario_Emisor = e.ID_Usuario
                LEFT JOIN usuario d ON r.ID_Usuario_Destinatario = d.ID_Usuario
                WHERE r.ID_Usuario_Emisor = ? OR r.ID_Usuario_Destinatario = ?
                ORDER BY r.fecha_hora DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $userId, $userId);
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
            $reportes[] = $row;
        }
        
        return $reportes;
    }

    public function obtenerReportePorId($reporteId) {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT r.*, 
                       e.nombre as emisor_nombre, e.apellido as emisor_apellido, e.email as emisor_email,
                       d.nombre as destinatario_nombre, d.apellido as destinatario_apellido, d.email as destinatario_email
                FROM reporte r
                JOIN usuario e ON r.ID_Usuario_Emisor = e.ID_Usuario
                LEFT JOIN usuario d ON r.ID_Usuario_Destinatario = d.ID_Usuario
                WHERE r.ID_Reporte = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $reporteId);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $reporte = $result->fetch_assoc();
        
        if ($reporte) {
            if (isset($reporte['emisor_email'])) {
                $reporte['emisor_email'] = CifradoHelper::desencriptar($reporte['emisor_email']);
            }
            if (isset($reporte['destinatario_email'])) {
                $reporte['destinatario_email'] = CifradoHelper::desencriptar($reporte['destinatario_email']);
            }
        }
        
        return $reporte;
    }

    public function actualizarEstadoReporte($reporteId, $estado) {
        $conn = $this->db->getConnection();
        
        $sql = "UPDATE reporte SET estado = ? WHERE ID_Reporte = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $estado, $reporteId);
        
        return $stmt->execute();
    }

    public function obtenerChat($emisorId, $destinatarioId) {
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
        $stmt->bind_param("ssss", $emisorId, $destinatarioId, $destinatarioId, $emisorId);
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
            $reportes[] = $row;
        }
        
        return $reportes;
    }

    public function obtenerUsuariosChat($userId) {
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
        $stmt->bind_param("sss", $userId, $userId, $userId);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $usuarios = [];
        
        while ($row = $result->fetch_assoc()) {
            if (isset($row['email'])) {
                $row['email'] = CifradoHelper::desencriptar($row['email']);
            }
            $usuarios[] = $row;
        }
        
        return $usuarios;
    }
}
?>