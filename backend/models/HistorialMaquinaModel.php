<?php
require_once __DIR__ . '/../config/database.php';

class HistorialMaquinaModel {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Registrar una actividad en una máquina
     */
    public function registrarActividad($data) {
        $conn = $this->db->getConnection();
        
        try {
            // La tabla tiene 11 columnas (ID_Historial se genera automáticamente)
            $sql = "INSERT INTO historial_maquinas (
                        ID_Maquina, ID_Usuario, tipo_usuario,
                        accion, descripcion, estado_anterior, estado_nuevo,
                        etapa_anterior, etapa_nueva, ip_address, detalles_adicionales
                    ) VALUES (
                        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
                    )";
            
            $stmt = $conn->prepare($sql);
            
            // Asegurarse de que detalles_adicionales sea un string JSON o null
            $detalles_json = null;
            if (!empty($data['detalles_adicionales'])) {
                if (is_array($data['detalles_adicionales'])) {
                    $detalles_json = json_encode($data['detalles_adicionales'], JSON_UNESCAPED_UNICODE);
                } else {
                    $detalles_json = $data['detalles_adicionales'];
                }
            }
            
            // CORRECCIÓN: 11 parámetros, no 12
            $stmt->bind_param(
                "sssssssssss", // 11 letras "s" (string)
                $data['ID_Maquina'],
                $data['ID_Usuario'],
                $data['tipo_usuario'],
                $data['accion'],
                $data['descripcion'],
                $data['estado_anterior'],
                $data['estado_nuevo'],
                $data['etapa_anterior'],
                $data['etapa_nueva'],
                $data['ip_address'],
                $detalles_json
            );
            
            $result = $stmt->execute();
            
            if (!$result) {
                error_log("Error SQL: " . $stmt->error);
            }
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Error en registrarActividad: " . $e->getMessage());
            error_log("Data: " . print_r($data, true));
            return false;
        }
    }

    /**
     * Obtener historial de una máquina específica
     */
    public function obtenerHistorialPorMaquina($idMaquina, $limite = 50, $offset = 0) {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT h.*, 
                       u.nombre as usuario_nombre, 
                       u.apellido as usuario_apellido,
                       u.tipo as usuario_tipo,
                       m.Nombre_Maquina
                FROM historial_maquinas h
                INNER JOIN usuario u ON h.ID_Usuario = u.ID_Usuario
                INNER JOIN MaquinaRecreativa m ON h.ID_Maquina = m.ID_Maquina
                WHERE h.ID_Maquina = ?
                ORDER BY h.fecha_hora DESC
                LIMIT ? OFFSET ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sii", $idMaquina, $limite, $offset);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $historial = [];
        
        while ($row = $result->fetch_assoc()) {
            if ($row['detalles_adicionales']) {
                $row['detalles_adicionales'] = json_decode($row['detalles_adicionales'], true);
            }
            $historial[] = $row;
        }
        
        return $historial;
    }

    /**
     * Obtener historial de un usuario específico
     */
    public function obtenerHistorialPorUsuario($idUsuario, $limite = 50, $offset = 0) {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT h.*, 
                       u.nombre as usuario_nombre,
                       u.apellido as usuario_apellido,
                       m.Nombre_Maquina
                FROM historial_maquinas h
                INNER JOIN usuario u ON h.ID_Usuario = u.ID_Usuario
                INNER JOIN MaquinaRecreativa m ON h.ID_Maquina = m.ID_Maquina
                WHERE h.ID_Usuario = ?
                ORDER BY h.fecha_hora DESC
                LIMIT ? OFFSET ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sii", $idUsuario, $limite, $offset);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $historial = [];
        
        while ($row = $result->fetch_assoc()) {
            if ($row['detalles_adicionales']) {
                $row['detalles_adicionales'] = json_decode($row['detalles_adicionales'], true);
            }
            $historial[] = $row;
        }
        
        return $historial;
    }

    /**
     * Obtener historial general con filtros
     */
    public function obtenerHistorialGeneral($filtros = [], $limite = 100, $offset = 0) {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT h.*, 
                       u.nombre as usuario_nombre,
                       u.apellido as usuario_apellido,
                       u.tipo as usuario_tipo,
                       m.Nombre_Maquina,
                       c.Nombre as NombreComercio
                FROM historial_maquinas h
                INNER JOIN usuario u ON h.ID_Usuario = u.ID_Usuario
                INNER JOIN MaquinaRecreativa m ON h.ID_Maquina = m.ID_Maquina
                LEFT JOIN Comercio c ON m.ID_Comercio = c.ID_Comercio
                WHERE 1=1";
        
        $params = [];
        $types = "";
        
        if (!empty($filtros['id_maquina'])) {
            $sql .= " AND h.ID_Maquina = ?";
            $params[] = $filtros['id_maquina'];
            $types .= "s";
        }
        
        if (!empty($filtros['id_usuario'])) {
            $sql .= " AND h.ID_Usuario = ?";
            $params[] = $filtros['id_usuario'];
            $types .= "s";
        }
        
        if (!empty($filtros['tipo_usuario'])) {
            $sql .= " AND h.tipo_usuario = ?";
            $params[] = $filtros['tipo_usuario'];
            $types .= "s";
        }
        
        if (!empty($filtros['accion'])) {
            $sql .= " AND h.accion LIKE ?";
            $params[] = "%{$filtros['accion']}%";
            $types .= "s";
        }
        
        if (!empty($filtros['fecha_inicio'])) {
            $sql .= " AND DATE(h.fecha_hora) >= ?";
            $params[] = $filtros['fecha_inicio'];
            $types .= "s";
        }
        
        if (!empty($filtros['fecha_fin'])) {
            $sql .= " AND DATE(h.fecha_hora) <= ?";
            $params[] = $filtros['fecha_fin'];
            $types .= "s";
        }
        
        $sql .= " ORDER BY h.fecha_hora DESC LIMIT ? OFFSET ?";
        $params[] = $limite;
        $params[] = $offset;
        $types .= "ii";
        
        $stmt = $conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        
        $result = $stmt->get_result();
        $historial = [];
        
        while ($row = $result->fetch_assoc()) {
            if ($row['detalles_adicionales']) {
                $row['detalles_adicionales'] = json_decode($row['detalles_adicionales'], true);
            }
            $historial[] = $row;
        }
        
        return $historial;
    }

    /**
     * Obtener total de registros para paginación
     */
    public function contarHistorial($filtros = []) {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT COUNT(*) as total FROM historial_maquinas h WHERE 1=1";
        $params = [];
        $types = "";
        
        if (!empty($filtros['id_maquina'])) {
            $sql .= " AND h.ID_Maquina = ?";
            $params[] = $filtros['id_maquina'];
            $types .= "s";
        }
        
        if (!empty($filtros['id_usuario'])) {
            $sql .= " AND h.ID_Usuario = ?";
            $params[] = $filtros['id_usuario'];
            $types .= "s";
        }
        
        $stmt = $conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return $row['total'];
    }
}
?>