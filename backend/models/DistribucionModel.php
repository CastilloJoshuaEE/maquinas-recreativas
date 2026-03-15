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
        
        // CORREGIDO: Usar 'estado' en lugar de 'estado_maquina'
        $sql = "UPDATE informe_distribucion SET estado = ? WHERE ID_Maquina = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $estado, $idMaquina);
        
        return $stmt->execute();
    }

    public function crearInformeDistribucion($idMaquina, $idUsuario, $idComercio) {
        $conn = $this->db->getConnection();
        
        try {
            // Verificar si ya existe un informe
            $checkSql = "SELECT ID_Distribucion FROM informe_distribucion WHERE ID_Maquina = ?";
            $checkStmt = $conn->prepare($checkSql);
            $checkStmt->bind_param("s", $idMaquina);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();

            if ($checkResult->num_rows > 0) {
                // Actualizar existente - CORREGIDO: Usar nombres correctos de columnas
                $updateSql = "UPDATE informe_distribucion 
                              SET ID_Usuario_Comprobador = ?, ID_Comercio = ?, fecha_alta = NOW() 
                              WHERE ID_Maquina = ?";
                $updateStmt = $conn->prepare($updateSql);
                $updateStmt->bind_param("sss", $idUsuario, $idComercio, $idMaquina);
                return $updateStmt->execute();
            } else {
                // Crear nuevo - CORREGIDO: Usar nombres correctos de columnas
                $insertSql = "INSERT INTO informe_distribucion 
                              (ID_Maquina, ID_Usuario_Comprobador, ID_Comercio, fecha_alta, estado) 
                              VALUES (?, ?, ?, NOW(), 'Distribuyendose')";
                $insertStmt = $conn->prepare($insertSql);
                $insertStmt->bind_param("sss", $idMaquina, $idUsuario, $idComercio);
                return $insertStmt->execute();
            }

        } catch (Exception $e) {
            error_log("Error en crearInformeDistribucion: " . $e->getMessage());
            return false;
        }
    }
// En DistribucionModel.php - CORREGIR método obtenerInformesDistribucion
public function obtenerInformesDistribucion($filters = []) {
    $conn = $this->db->getConnection();
    
    $estado = $filters['estado'] ?? null;
    $idComercio = $filters['ID_Comercio'] ?? null;
    $fechaInicio = $filters['fecha_inicio'] ?? null;
    $fechaFin = $filters['fecha_fin'] ?? null;
    $idMaquina = $filters['ID_Maquina'] ?? null;
    
    $sql = "SELECT 
                id.ID_Distribucion,
                id.ID_Maquina,
                id.ID_Usuario_Comprobador,
                id.ID_Comercio,
                id.fecha_alta,
                id.fecha_baja,
                id.estado,
                m.Nombre_Maquina,
                CONCAT(u.nombre, ' ', u.apellido) as Nombre_Tecnico,
                c.Nombre as Nombre_Comercio,
                c.Direccion as Direccion_Comercio,
                c.Telefono as Telefono_Comercio,
                c.Tipo as Tipo_Comercio
            FROM informe_distribucion id
            INNER JOIN MaquinaRecreativa m ON id.ID_Maquina = m.ID_Maquina
            INNER JOIN usuario u ON id.ID_Usuario_Comprobador = u.ID_Usuario
            INNER JOIN Comercio c ON id.ID_Comercio = c.ID_Comercio
            WHERE 1=1";
    
    $params = [];
    $types = "";

    if ($estado) {
        $sql .= " AND id.estado = ?";
        $params[] = $estado;
        $types .= "s";
    }
    if ($idComercio) {
        $sql .= " AND id.ID_Comercio = ?";
        $params[] = $idComercio;
        $types .= "s";
    }
    if ($fechaInicio) {
        $sql .= " AND DATE(id.fecha_alta) >= ?";
        $params[] = $fechaInicio;
        $types .= "s";
    }
    if ($fechaFin) {
        $sql .= " AND DATE(id.fecha_alta) <= ?";
        $params[] = $fechaFin;
        $types .= "s";
    }
    if ($idMaquina) {
        $sql .= " AND id.ID_Maquina = ?";
        $params[] = $idMaquina;
        $types .= "s";
    }

    $sql .= " ORDER BY id.fecha_alta DESC";

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
}
?>