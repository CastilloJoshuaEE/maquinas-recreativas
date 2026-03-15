<?php
require_once __DIR__ . '/../config/database.php';

class NotificacionModel {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

public function crearNotificacion($idRemitente, $idDestinatario, $idMaquina, $tipo, $mensaje) {
    $conn = $this->db->getConnection();
    
    try {
        $sql = "INSERT INTO NotificacionMaquinaRecreativa 
                (ID_Notificacion, ID_Remitente, ID_Destinatario, ID_Maquina, Tipo, Mensaje, Fecha, Estado) 
                VALUES (UUID(), ?, ?, ?, ?, ?, NOW(), 'No leido')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $idRemitente, $idDestinatario, $idMaquina, $tipo, $mensaje);
        
        return $stmt->execute();

    } catch (Exception $e) {
        error_log("Error en crearNotificacion: " . $e->getMessage());
        return false;
    }
}
    public function crearNotificacionReporte($reporteId, $usuarioId, $mensaje) {
    $conn = $this->db->getConnection();
    
    try {
        // Verificar que el reporte existe antes de crear la notificación
        $checkReporte = $conn->query("SELECT ID_Reporte FROM reporte WHERE ID_Reporte = '$reporteId'");
        if ($checkReporte->num_rows == 0) {
            error_log("Error: El reporte con ID $reporteId no existe");
            return false;
        }
        
        $sql = "INSERT INTO notificaciones (ID_Notificaciones, ID_Reporte, ID_Usuario, mensaje, fecha_hora, leida) 
                VALUES (UUID(), ?, ?, ?, NOW(), 0)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $reporteId, $usuarioId, $mensaje);
        
        $result = $stmt->execute();
        
        if (!$result) {
            error_log("Error SQL en crearNotificacionReporte: " . $conn->error);
        }
        
        return $result;
        
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
    
    try {
        // Log para depuración
        error_log("Model - Intentando marcar notificación: $notificacionId para usuario: $usuarioId");
        
        // Validar que los IDs no estén vacíos
        if (empty($notificacionId) || empty($usuarioId)) {
            error_log("IDs inválidos");
            return false;
        }

        // Primero verificar que la notificación existe y pertenece al usuario
        $checkSql = "SELECT ID_Notificaciones, leida FROM notificaciones 
                     WHERE ID_Notificaciones = ? AND ID_Usuario = ?";
        $checkStmt = $conn->prepare($checkSql);
        
        if (!$checkStmt) {
            error_log("Error preparando consulta check: " . $conn->error);
            return false;
        }
        
        $checkStmt->bind_param("ss", $notificacionId, $usuarioId);
        
        if (!$checkStmt->execute()) {
            error_log("Error ejecutando consulta check: " . $checkStmt->error);
            $checkStmt->close();
            return false;
        }
        
        $result = $checkStmt->get_result();
        
        if ($result->num_rows === 0) {
            error_log("Notificación no encontrada o no pertenece al usuario");
            $checkStmt->close();
            return false;
        }
        
        $row = $result->fetch_assoc();
        $checkStmt->close();
        
        if ($row['leida'] == 1) {
            error_log("La notificación ya estaba marcada como leída");
            return true; // Ya está leída, consideramos éxito
        }
        
        // Marcar como leída
        $sql = "UPDATE notificaciones SET leida = 1 
                WHERE ID_Notificaciones = ? AND ID_Usuario = ?";
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            error_log("Error preparando consulta update: " . $conn->error);
            return false;
        }
        
        $stmt->bind_param("ss", $notificacionId, $usuarioId);
        
        if (!$stmt->execute()) {
            error_log("Error SQL en marcarComoLeidaNotificacion: " . $stmt->error);
            $stmt->close();
            return false;
        }
        
        $affectedRows = $conn->affected_rows;
        error_log("Filas afectadas: $affectedRows");
        
        $stmt->close();
        return $affectedRows > 0;

    } catch (Exception $e) {
        error_log("Error en marcarComoLeidaNotificacion model: " . $e->getMessage());
        return false;
    }
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