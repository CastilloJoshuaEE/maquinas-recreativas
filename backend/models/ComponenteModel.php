<?php
require_once __DIR__ . '/../config/database.php';

class ComponenteModel {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function obtenerComponentes($tipo = null, $limit = 10, $offset = 0) {
        $conn = $this->db->getConnection();
        
        // Consulta para obtener componentes
        $sql = "SELECT c.*, 
                       CASE WHEN ce.ID_Componente IS NOT NULL THEN 1 ELSE 0 END as en_uso
                FROM componente c
                LEFT JOIN componente_en_uso ce ON c.ID_Componente = ce.ID_Componente
                WHERE 1=1";
        $params = [];
        $types = "";

        if ($tipo && $tipo !== 'todos') {
            $sql .= " AND c.tipo = ?";
            $params[] = $tipo;
            $types .= "s";
        }

        $sql .= " LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        $types .= "ii";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $componentes = [];
        while ($row = $result->fetch_assoc()) {
            $componentes[] = $row;
        }

        // Consulta para contar total
        $countSql = "SELECT COUNT(*) as total FROM componente";
        if ($tipo && $tipo !== 'todos') {
            $countSql .= " WHERE tipo = '$tipo'";
        }
        $countResult = $conn->query($countSql);
        $total = $countResult->fetch_assoc()['total'];
        
        return [
            'componentes' => $componentes,
            'total' => $total
        ];
    }
 public function obtenerComponentesDisponibles($tipo = null) {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT c.* FROM componente c
                LEFT JOIN componente_usuario cu ON c.ID_Componente = cu.ID_Componente
                WHERE cu.ID_Componente IS NULL";
        $params = [];
        $types = "";

        if ($tipo && $tipo !== 'todos') {
            $sql .= " AND c.tipo = ?";
            $params[] = $tipo;
            $types .= "s";
        }

        $sql .= " ORDER BY c.nombre ASC";

        $stmt = $conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        
        $componentes = [];
        while ($row = $result->fetch_assoc()) {
            $componentes[] = $row;
        }
        
        return $componentes;
    }

    public function usarComponente($idComponente, $idUsuario, $idMaquina = null) {
        $conn = $this->db->getConnection();
        
        try {
            $conn->begin_transaction();

            // Verificar si el componente está disponible
            $checkSql = "SELECT COUNT(*) as count FROM componente_en_uso WHERE ID_Componente = ?";
            $checkStmt = $conn->prepare($checkSql);
            $checkStmt->bind_param("s", $idComponente);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            $checkRow = $checkResult->fetch_assoc();

            if ($checkRow['count'] > 0) {
                $conn->rollback();
                return [
                    'success' => false,
                    'message' => 'El componente ya está en uso'
                ];
            }

            // Insertar en componente_en_uso
            $insertSql = "INSERT INTO componente_en_uso (ID_Componente, ID_Usuario, ID_Maquina, fecha_asignacion) 
                          VALUES (?, ?, ?, NOW())";
            $insertStmt = $conn->prepare($insertSql);
            $insertStmt->bind_param("sss", $idComponente, $idUsuario, $idMaquina);
            
            if (!$insertStmt->execute()) {
                $conn->rollback();
                return [
                    'success' => false,
                    'message' => 'Error al asignar el componente'
                ];
            }

            $conn->commit();
            return [
                'success' => true,
                'message' => 'Componente asignado correctamente'
            ];

        } catch (Exception $e) {
            $conn->rollback();
            error_log("Error en usarComponente: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error en la base de datos'
            ];
        }
    }

    public function liberarComponente($idComponente, $idUsuario) {
        $conn = $this->db->getConnection();
        
        try {
            $conn->begin_transaction();

            // Verificar que el componente está asignado al usuario
            $checkSql = "SELECT COUNT(*) as count FROM componente_en_uso 
                         WHERE ID_Componente = ? AND ID_Usuario = ?";
            $checkStmt = $conn->prepare($checkSql);
            $checkStmt->bind_param("ss", $idComponente, $idUsuario);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            $checkRow = $checkResult->fetch_assoc();

            if ($checkRow['count'] == 0) {
                $conn->rollback();
                return [
                    'success' => false,
                    'message' => 'El componente no está asignado a este usuario'
                ];
            }

            // Eliminar de componente_en_uso
            $deleteSql = "DELETE FROM componente_en_uso WHERE ID_Componente = ? AND ID_Usuario = ?";
            $deleteStmt = $conn->prepare($deleteSql);
            $deleteStmt->bind_param("ss", $idComponente, $idUsuario);
            $deleteStmt->execute();

            $conn->commit();
            return [
                'success' => true,
                'message' => 'Componente liberado correctamente'
            ];

        } catch (Exception $e) {
            $conn->rollback();
            error_log("Error en liberarComponente: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error en la base de datos'
            ];
        }
    }

    public function obtenerComponentesEnUso($idUsuario, $idMaquina = null) {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT c.*, ce.fecha_asignacion, ce.ID_Maquina, m.nombre as nombre_maquina
                FROM componente_en_uso ce
                JOIN componente c ON ce.ID_Componente = c.ID_Componente
                LEFT JOIN maquina m ON ce.ID_Maquina = m.ID_Maquina
                WHERE ce.ID_Usuario = ?";
        $params = [$idUsuario];
        $types = "s";

        if ($idMaquina) {
            $sql .= " AND ce.ID_Maquina = ?";
            $params[] = $idMaquina;
            $types .= "s";
        }

        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $componentes = [];
        while ($row = $result->fetch_assoc()) {
            $componentes[] = $row;
        }
        
        return $componentes;
    }

    public function liberarComponentesCancelacion($idPlaca, $idCarcasa, $idUsuario) {
        $conn = $this->db->getConnection();
        
        try {
            $conn->begin_transaction();

            $componentesLiberados = 0;

            if ($idPlaca) {
                $deletePlacaSql = "DELETE FROM componente_en_uso WHERE ID_Componente = ? AND ID_Usuario = ?";
                $deletePlacaStmt = $conn->prepare($deletePlacaSql);
                $deletePlacaStmt->bind_param("ss", $idPlaca, $idUsuario);
                $deletePlacaStmt->execute();
                $componentesLiberados += $deletePlacaStmt->affected_rows;
            }

            if ($idCarcasa) {
                $deleteCarcasaSql = "DELETE FROM componente_en_uso WHERE ID_Componente = ? AND ID_Usuario = ?";
                $deleteCarcasaStmt = $conn->prepare($deleteCarcasaSql);
                $deleteCarcasaStmt->bind_param("ss", $idCarcasa, $idUsuario);
                $deleteCarcasaStmt->execute();
                $componentesLiberados += $deleteCarcasaStmt->affected_rows;
            }

            $conn->commit();
            
            if ($componentesLiberados > 0) {
                return [
                    'success' => true,
                    'message' => "$componentesLiberados componente(s) liberado(s) correctamente"
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'No se encontraron componentes para liberar'
                ];
            }

        } catch (Exception $e) {
            $conn->rollback();
            error_log("Error en liberarComponentesCancelacion: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error en la base de datos'
            ];
        }
    }

    public function asignarCarcasa($idComponente, $idUsuario) {
        $conn = $this->db->getConnection();
        
        try {
            $conn->begin_transaction();

            // Verificar si el componente está disponible
            $checkSql = "SELECT COUNT(*) as count FROM componente_en_uso WHERE ID_Componente = ?";
            $checkStmt = $conn->prepare($checkSql);
            $checkStmt->bind_param("s", $idComponente);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            $checkRow = $checkResult->fetch_assoc();

            if ($checkRow['count'] > 0) {
                $conn->rollback();
                return [
                    'success' => false,
                    'message' => 'El componente ya está en uso'
                ];
            }

            // Asignar componente
            $insertSql = "INSERT INTO componente_en_uso (ID_Componente, ID_Usuario, fecha_asignacion) 
                          VALUES (?, ?, NOW())";
            $insertStmt = $conn->prepare($insertSql);
            $insertStmt->bind_param("ss", $idComponente, $idUsuario);
            
            if (!$insertStmt->execute()) {
                $conn->rollback();
                return [
                    'success' => false,
                    'message' => 'Error al asignar la carcasa'
                ];
            }

            $conn->commit();
            return [
                'success' => true,
                'message' => 'Carcasa asignada correctamente'
            ];

        } catch (Exception $e) {
            $conn->rollback();
            error_log("Error en asignarCarcasa: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error en la base de datos'
            ];
        }
    }
}
?>