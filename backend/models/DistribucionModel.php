<?php
require_once __DIR__ . '/../config/database.php';

class DistribucionModel {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function actualizarInformeDistribucion($idMaquina, $estado) {
        $conn = $this->db->getConnection();
        
        $estadosPermitidos = ['Operativa', 'Retirada', 'No operativa'];
        if (!in_array($estado, $estadosPermitidos)) {
            $estado = 'Operativa';
        }
        
        $sql = "UPDATE informe_distribucion SET estado_maquina = ? WHERE ID_Maquina = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $estado, $idMaquina);
        
        return $stmt->execute();
    }

    public function crearInformeDistribucion($idMaquina, $idUsuario, $idComercio) {
        $conn = $this->db->getConnection();
        
        try {
            // Verificar si ya existe un informe
            $checkSql = "SELECT ID_Informe_Distribucion FROM informe_distribucion WHERE ID_Maquina = ?";
            $checkStmt = $conn->prepare($checkSql);
            $checkStmt->bind_param("s", $idMaquina);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();

            if ($checkResult->num_rows > 0) {
                // Actualizar existente
                $updateSql = "UPDATE informe_distribucion 
                              SET ID_Usuario = ?, ID_Comercio = ?, fecha_actualizacion = NOW() 
                              WHERE ID_Maquina = ?";
                $updateStmt = $conn->prepare($updateSql);
                $updateStmt->bind_param("sss", $idUsuario, $idComercio, $idMaquina);
                return $updateStmt->execute();
            } else {
                // Crear nuevo
                $idInforme = $this->generateUUID($conn);
                $insertSql = "INSERT INTO informe_distribucion (ID_Informe_Distribucion, ID_Maquina, ID_Usuario, ID_Comercio, fecha_creacion, estado_maquina) 
                              VALUES (?, ?, ?, ?, NOW(), 'Operativa')";
                $insertStmt = $conn->prepare($insertSql);
                $insertStmt->bind_param("ssss", $idInforme, $idMaquina, $idUsuario, $idComercio);
                return $insertStmt->execute();
            }

        } catch (Exception $e) {
            error_log("Error en crearInformeDistribucion: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerInformesDistribucion($filters = []) {
        $conn = $this->db->getConnection();
        
        $estado = $filters['estado'] ?? null;
        $idComercio = $filters['ID_Comercio'] ?? null;
        $fechaInicio = $filters['fecha_inicio'] ?? null;
        $fechaFin = $filters['fecha_fin'] ?? null;
        $idMaquina = $filters['ID_Maquina'] ?? null;
        
        $sql = "SELECT id.*, m.nombre as nombre_maquina, c.nombre as nombre_comercio, 
                       u.nombre as nombre_usuario, u.apellido as apellido_usuario
                FROM informe_distribucion id
                JOIN maquina m ON id.ID_Maquina = m.ID_Maquina
                JOIN comercio c ON id.ID_Comercio = c.ID_Comercio
                JOIN usuario u ON id.ID_Usuario = u.ID_Usuario
                WHERE 1=1";
        $params = [];
        $types = "";

        if ($estado) {
            $sql .= " AND id.estado_maquina = ?";
            $params[] = $estado;
            $types .= "s";
        }
        if ($idComercio) {
            $sql .= " AND id.ID_Comercio = ?";
            $params[] = $idComercio;
            $types .= "s";
        }
        if ($fechaInicio) {
            $sql .= " AND DATE(id.fecha_creacion) >= ?";
            $params[] = $fechaInicio;
            $types .= "s";
        }
        if ($fechaFin) {
            $sql .= " AND DATE(id.fecha_creacion) <= ?";
            $params[] = $fechaFin;
            $types .= "s";
        }
        if ($idMaquina) {
            $sql .= " AND id.ID_Maquina = ?";
            $params[] = $idMaquina;
            $types .= "s";
        }

        $sql .= " ORDER BY id.fecha_creacion DESC";

        $stmt = $conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        
        $informes = [];
        while ($row = $result->fetch_assoc()) {
            $informes[] = $row;
        }
        
        return $informes;
    }

    private function generateUUID($conn) {
        $sql = "SELECT UUID() as uuid";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        return $row['uuid'];
    }
}
?>