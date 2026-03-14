<?php
require_once __DIR__ . '/../config/database.php';

class InformeModel {
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

    public function registrarRecaudacion($data) {
        $conn = $this->db->getConnection();
        
        $detalle = $data['detalle'] ?? '';
        $porcentaje = $data['Porcentaje_Comercio'] ?? 0;
        
        try {
            $idRecaudacion = $this->generateUUID($conn);
            
            $sql = "INSERT INTO recaudacion (
                        ID_Recaudacion, Tipo_Comercio, ID_Maquina, ID_Usuario, 
                        Monto_Total, Monto_Empresa, Monto_Comercio, fecha, 
                        detalle, Porcentaje_Comercio
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            
            $stmt->bind_param(
                "ssssddddss",
                $idRecaudacion,
                $data['Tipo_Comercio'],
                $data['ID_Maquina'],
                $data['ID_Usuario'],
                $data['Monto_Total'],
                $data['Monto_Empresa'],
                $data['Monto_Comercio'],
                $data['fecha'],
                $detalle,
                $porcentaje
            );

            if ($stmt->execute()) {
                return $idRecaudacion;
            }
            return false;

        } catch (Exception $e) {
            error_log("Error en registrarRecaudacion: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerRecaudaciones($filters) {
        $conn = $this->db->getConnection();
        
        $fechaInicio = $filters['fecha_inicio'] ?? null;
        $fechaFin = $filters['fecha_fin'] ?? null;
        $idMaquina = $filters['ID_Maquina'] ?? null;
        $tipoComercio = $filters['Tipo_Comercio'] ?? null;
        
        $sql = "SELECT r.*, m.nombre as nombre_maquina, c.nombre as nombre_comercio,
                       u.nombre as nombre_usuario, u.apellido as apellido_usuario
                FROM recaudacion r
                JOIN maquina m ON r.ID_Maquina = m.ID_Maquina
                JOIN comercio c ON m.ID_Comercio = c.ID_Comercio
                JOIN usuario u ON r.ID_Usuario = u.ID_Usuario
                WHERE 1=1";
        $params = [];
        $types = "";

        if ($fechaInicio) {
            $sql .= " AND DATE(r.fecha) >= ?";
            $params[] = $fechaInicio;
            $types .= "s";
        }
        if ($fechaFin) {
            $sql .= " AND DATE(r.fecha) <= ?";
            $params[] = $fechaFin;
            $types .= "s";
        }
        if ($idMaquina) {
            $sql .= " AND r.ID_Maquina = ?";
            $params[] = $idMaquina;
            $types .= "s";
        }
        if ($tipoComercio) {
            $sql .= " AND r.Tipo_Comercio = ?";
            $params[] = $tipoComercio;
            $types .= "s";
        }

        $sql .= " ORDER BY r.fecha DESC";

        $stmt = $conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        
        $recaudaciones = [];
        while ($row = $result->fetch_assoc()) {
            $recaudaciones[] = $row;
        }
        
        return $recaudaciones;
    }

    public function obtenerResumenRecaudacionesLimitado($limit) {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT DATE(fecha) as fecha, 
                       COUNT(*) as cantidad,
                       SUM(Monto_Total) as total_recaudado,
                       SUM(Monto_Empresa) as total_empresa,
                       SUM(Monto_Comercio) as total_comercio
                FROM recaudacion
                GROUP BY DATE(fecha)
                ORDER BY fecha DESC
                LIMIT ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $resumen = [];
        
        while ($row = $result->fetch_assoc()) {
            $resumen[] = $row;
        }
        
        return $resumen;
    }

    public function actualizarRecaudacion($data) {
        $conn = $this->db->getConnection();
        
        try {
            $detalle = $data['detalle'] ?? '';
            $porcentaje = $data['Porcentaje_Comercio'] ?? 0;
            
            $sql = "UPDATE recaudacion SET 
                    Tipo_Comercio = ?,
                    ID_Maquina = ?,
                    Monto_Total = ?,
                    Monto_Empresa = ?,
                    Monto_Comercio = ?,
                    fecha = ?,
                    detalle = ?,
                    Porcentaje_Comercio = ?
                    WHERE ID_Recaudacion = ?";
            $stmt = $conn->prepare($sql);
            
            $stmt->bind_param(
                "ssddddsss",
                $data['Tipo_Comercio'],
                $data['ID_Maquina'],
                $data['Monto_Total'],
                $data['Monto_Empresa'],
                $data['Monto_Comercio'],
                $data['fecha'],
                $detalle,
                $porcentaje,
                $data['ID_Recaudacion']
            );
            
            $success = $stmt->execute();
            $affectedRows = $stmt->affected_rows;
            
            return [
                'success' => $success,
                'affected_rows' => $affectedRows,
                'message' => $success ? '' : ($stmt->error ?? 'Error desconocido')
            ];
            
        } catch (Exception $e) {
            error_log("Error en actualizarRecaudacion: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error en la base de datos: ' . $e->getMessage()
            ];
        }
    }

    public function eliminarRecaudacion($id) {
        $conn = $this->db->getConnection();
        
        try {
            $conn->begin_transaction();

            // Eliminar informe principal asociado
            $deleteInformeSql = "DELETE FROM informe_principal WHERE ID_Recaudacion = ?";
            $deleteInformeStmt = $conn->prepare($deleteInformeSql);
            $deleteInformeStmt->bind_param("s", $id);
            $deleteInformeStmt->execute();

            // Eliminar recaudación
            $sql = "DELETE FROM recaudacion WHERE ID_Recaudacion = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $id);
            $result = $stmt->execute();

            $conn->commit();
            return $result;

        } catch (Exception $e) {
            $conn->rollback();
            error_log("Error en eliminarRecaudacion: " . $e->getMessage());
            return false;
        }
    }

    public function guardarInformePrincipal($data) {
        $conn = $this->db->getConnection();
        
        try {
            $idInforme = $this->generateUUID($conn);
            
            $sql = "INSERT INTO informe_principal (
                        ID_Informe_Principal, ID_Recaudacion, CI_Usuario, 
                        Nombre_Maquina, ID_Comercio, Nombre_Comercio, 
                        Direccion_Comercio, Telefono_Comercio, Pago_Ensamblador, 
                        Pago_Comprobador, Pago_Mantenimiento, empresa_nombre, 
                        empresa_descripcion
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            
            $stmt->bind_param(
                "ssssssssdddss",
                $idInforme,
                $data['ID_Recaudacion'],
                $data['CI_Usuario'],
                $data['Nombre_Maquina'],
                $data['ID_Comercio'],
                $data['Nombre_Comercio'],
                $data['Direccion_Comercio'],
                $data['Telefono_Comercio'],
                $data['Pago_Ensamblador'],
                $data['Pago_Comprobador'],
                $data['Pago_Mantenimiento'],
                $data['empresa_nombre'],
                $data['empresa_descripcion']
            );

            if ($stmt->execute()) {
                return $idInforme;
            }
            
            error_log("Error en guardarInformePrincipal: " . $stmt->error);
            return false;

        } catch (Exception $e) {
            error_log("Error en guardarInformePrincipal: " . $e->getMessage());
            return false;
        }
    }

    public function guardarDetalleComponente($idInforme, $idComponente) {
        $conn = $this->db->getConnection();
        
        try {
            $idDetalle = $this->generateUUID($conn);
            
            $sql = "INSERT INTO detalle_componente_informe (ID_Detalle, ID_Informe_Principal, ID_Componente) 
                    VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $idDetalle, $idInforme, $idComponente);
            
            return $stmt->execute();

        } catch (Exception $e) {
            error_log("Error en guardarDetalleComponente: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerInformePrincipal($idRecaudacion) {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT * FROM informe_principal WHERE ID_Recaudacion = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $idRecaudacion);
        $stmt->execute();
        
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return false;
    }

    public function obtenerComponentesInforme($idInforme) {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT c.* 
                FROM detalle_componente_informe dci
                JOIN componente c ON dci.ID_Componente = c.ID_Componente
                WHERE dci.ID_Informe_Principal = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $idInforme);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $componentes = [];
        
        while ($row = $result->fetch_assoc()) {
            $componentes[] = $row;
        }
        
        return $componentes;
    }

    public function obtenerRecaudacion($idRecaudacion) {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT r.*, m.nombre as nombre_maquina, c.nombre as nombre_comercio,
                       u.nombre as nombre_usuario, u.apellido as apellido_usuario
                FROM recaudacion r
                JOIN maquina m ON r.ID_Maquina = m.ID_Maquina
                JOIN comercio c ON m.ID_Comercio = c.ID_Comercio
                JOIN usuario u ON r.ID_Usuario = u.ID_Usuario
                WHERE r.ID_Recaudacion = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $idRecaudacion);
        $stmt->execute();
        
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return false;
    }

    public function obtenerComercioPorId($idComercio) {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT * FROM Comercio WHERE ID_Comercio = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $idComercio);
        $stmt->execute();
        
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return false;
    }
}
?>