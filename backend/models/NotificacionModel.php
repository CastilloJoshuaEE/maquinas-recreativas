<?php
require_once __DIR__ . '/../config/database.php';

class NotificacionModel {
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

    public function crearNotificacion($idRemitente, $idDestinatario, $idMaquina, $tipo, $mensaje) {
        $conn = $this->db->getConnection();
        
        try {
            $idNotificacion = $this->generateUUID($conn);
            
            // CORRECCIÓN: Usar el nombre correcto de la tabla (NotificacionMaquinaRecreativa)
            $sql = "INSERT INTO NotificacionMaquinaRecreativa (ID_Notificacion, ID_Remitente, ID_Destinatario, ID_Maquina, Tipo, Mensaje, Fecha, Estado) 
                    VALUES (?, ?, ?, ?, ?, ?, NOW(), 'No leido')";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssss", $idNotificacion, $idRemitente, $idDestinatario, $idMaquina, $tipo, $mensaje);
            
            return $stmt->execute();

        } catch (Exception $e) {
            error_log("Error en crearNotificacion: " . $e->getMessage());
            return false;
        }
    }

    public function crearNotificacionReporte($reporteId, $usuarioId, $mensaje) {
        $conn = $this->db->getConnection();
        
        try {
            $idNotificacion = $this->generateUUID($conn);
            
            // CORRECCIÓN: Usar la tabla notificaciones
            $sql = "INSERT INTO notificaciones (ID_Notificaciones, ID_Reporte, ID_Usuario, mensaje, fecha_hora, leida) 
                    VALUES (?, ?, ?, ?, NOW(), 0)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssss", $idNotificacion, $reporteId, $usuarioId, $mensaje);
            
            return $stmt->execute();

        } catch (Exception $e) {
            error_log("Error en crearNotificacionReporte: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerNotificacionesPorUsuario($usuarioId) {
        $conn = $this->db->getConnection();
        
        try {
            $sql = "SELECT n.*, 
                           r.descripcion as reporte_descripcion
                    FROM notificaciones n
                    LEFT JOIN reporte r ON n.ID_Reporte = r.ID_Reporte
                    WHERE n.ID_Usuario = ?
                    ORDER BY n.fecha_hora DESC";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $usuarioId);
            $stmt->execute();
            
            $result = $stmt->get_result();
            $notificaciones = [];
            
            while ($row = $result->fetch_assoc()) {
                $notificaciones[] = $row;
            }
            
            return $notificaciones;

        } catch (Exception $e) {
            error_log("Error en obtenerNotificacionesPorUsuario: " . $e->getMessage());
            return [];
        }
    }

    public function obtenerNotificacionesPorDestinatario($idDestinatario) {
        $conn = $this->db->getConnection();
        
        try {
            $sql = "SELECT n.*, 
                           u.nombre as nombre_remitente, u.apellido as apellido_remitente,
                           m.Nombre_Maquina as nombre_maquina
                    FROM NotificacionMaquinaRecreativa n
                    LEFT JOIN usuario u ON n.ID_Remitente = u.ID_Usuario
                    LEFT JOIN MaquinaRecreativa m ON n.ID_Maquina = m.ID_Maquina
                    WHERE n.ID_Destinatario = ?
                    ORDER BY n.Fecha DESC";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $idDestinatario);
            $stmt->execute();
            
            $result = $stmt->get_result();
            $notificaciones = [];
            
            while ($row = $result->fetch_assoc()) {
                $notificaciones[] = $row;
            }
            
            return $notificaciones;

        } catch (Exception $e) {
            error_log("Error en obtenerNotificacionesPorDestinatario: " . $e->getMessage());
            return [];
        }
    }

    public function marcarComoLeida($idNotificacion) {
        $conn = $this->db->getConnection();
        
        $sql = "UPDATE NotificacionMaquinaRecreativa SET Estado = 'Leido' WHERE ID_Notificacion = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $idNotificacion);
        
        return $stmt->execute();
    }

    public function marcarComoLeidaNotificacion($notificacionId, $usuarioId) {
        $conn = $this->db->getConnection();
        
        $sql = "UPDATE notificaciones SET leida = 1 
                WHERE ID_Notificaciones = ? AND ID_Usuario = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $notificacionId, $usuarioId);
        
        return $stmt->execute();
    }

    public function obtenerNoLeidas($idUsuario) {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT COUNT(*) as total FROM NotificacionMaquinaRecreativa WHERE ID_Destinatario = ? AND Estado = 'No leido'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $idUsuario);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['total'];
    }

    public function obtenerCantidadNoLeidas($usuarioId) {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT COUNT(*) as cantidad FROM notificaciones WHERE ID_Usuario = ? AND leida = 0";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $usuarioId);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['cantidad'];
    }

    public function marcarTodasComoLeidas($usuarioId) {
        $conn = $this->db->getConnection();
        
        $sql = "UPDATE notificaciones SET leida = 1 WHERE ID_Usuario = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $usuarioId);
        
        return $stmt->execute();
    }
}
?>