<?php
require_once __DIR__ . '/../config/database.php';

class InformeModel {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }


    public function registrarRecaudacion($data) {
    $conn = $this->db->getConnection();
    
    $detalle = $data['detalle'] ?? '';
    $porcentaje = $data['Porcentaje_Comercio'] ?? 0;
    
    try {

        $sql = "INSERT INTO recaudaciones (
                    Tipo_Comercio, ID_Maquina, ID_Usuario,
                    Monto_Total, Monto_Empresa, Monto_Comercio, fecha,
                    detalle, Porcentaje_Comercio
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param(
            "sssdddsss",
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
            return true;
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
    $idRecaudacion = $filters['ID_Recaudacion'] ?? null;
    
    $sql = "SELECT 
                r.*,
                c.Nombre as Nombre_Comercio,
                m.Nombre_Maquina,
                u.nombre as nombre_usuario,
                u.apellido as apellido_usuario,
                (SELECT ID_Informe FROM informes_recaudacion WHERE ID_Recaudacion = r.ID_Recaudacion LIMIT 1) as ID_Informe
            FROM recaudaciones r
            INNER JOIN MaquinaRecreativa m ON r.ID_Maquina = m.ID_Maquina
            INNER JOIN Comercio c ON m.ID_Comercio = c.ID_Comercio
            INNER JOIN usuario u ON r.ID_Usuario = u.ID_Usuario
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
    if ($idRecaudacion) {
        $sql .= " AND r.ID_Recaudacion = ?";
        $params[] = $idRecaudacion;
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
        // Formatear la fecha correctamente
        $row['fecha'] = date('Y-m-d H:i:s', strtotime($row['fecha']));
        $row['Monto_Total'] = floatval($row['Monto_Total']);
        $row['Monto_Empresa'] = floatval($row['Monto_Empresa']);
        $row['Monto_Comercio'] = floatval($row['Monto_Comercio']);
        $row['Porcentaje_Comercio'] = floatval($row['Porcentaje_Comercio']);
        $recaudaciones[] = $row;
    }
    
    return $recaudaciones;
}
public function obtenerResumenRecaudacionesLimitado($limit = null) {
    $conn = $this->db->getConnection();
    
    // Si se pasa limit, se usa como límite, sino se traen todos
    if ($limit) {
        $sql = "SELECT 
                    Tipo_Comercio,
                    COUNT(*) as TotalRecaudaciones,
                    SUM(Monto_Total) as TotalRecaudado,
                    SUM(Monto_Empresa) as TotalEmpresa,
                    SUM(Monto_Comercio) as TotalComercio
                FROM recaudaciones
                GROUP BY Tipo_Comercio
                ORDER BY TotalRecaudado DESC
                LIMIT ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $limit);
    } else {
        $sql = "SELECT 
                    Tipo_Comercio,
                    COUNT(*) as TotalRecaudaciones,
                    SUM(Monto_Total) as TotalRecaudado,
                    SUM(Monto_Empresa) as TotalEmpresa,
                    SUM(Monto_Comercio) as TotalComercio
                FROM recaudaciones
                GROUP BY Tipo_Comercio
                ORDER BY TotalRecaudado DESC";
        $stmt = $conn->prepare($sql);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $resumen = [];
    
    while ($row = $result->fetch_assoc()) {
        $resumen[] = $row;
    }
    
    // Si no hay resultados, devolver un array con valores por defecto
    if (empty($resumen)) {
        $resumen[] = [
            'Tipo_Comercio' => 'Sin datos',
            'TotalRecaudaciones' => 0,
            'TotalRecaudado' => 0,
            'TotalEmpresa' => 0,
            'TotalComercio' => 0
        ];
    }
    
    return $resumen;
}
    public function actualizarRecaudacion($data) {
        $conn = $this->db->getConnection();
        
        try {
            $detalle = $data['detalle'] ?? '';
            $porcentaje = $data['Porcentaje_Comercio'] ?? 0;
            
            $sql = "UPDATE recaudaciones SET 
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

        // Primero eliminar los detalles del informe
        $deleteDetallesSql = "DELETE d FROM informe_detalle d 
                              INNER JOIN informes_recaudacion i ON d.ID_Informe = i.ID_Informe
                              WHERE i.ID_Recaudacion = ?";
        $deleteDetallesStmt = $conn->prepare($deleteDetallesSql);
        $deleteDetallesStmt->bind_param("s", $id);
        $deleteDetallesStmt->execute();

        // Eliminar informe principal asociado
        $deleteInformeSql = "DELETE FROM informes_recaudacion WHERE ID_Recaudacion = ?";
        $deleteInformeStmt = $conn->prepare($deleteInformeSql);
        $deleteInformeStmt->bind_param("s", $id);
        $deleteInformeStmt->execute();

        // Eliminar recaudación
        $sql = "DELETE FROM recaudaciones WHERE ID_Recaudacion = ?";
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
        // Verificar si ya existe un informe para esta recaudación
        $checkSql = "SELECT ID_Informe FROM informes_recaudacion WHERE ID_Recaudacion = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("s", $data['ID_Recaudacion']);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows > 0) {
            // Actualizar informe existente
            $sql = "UPDATE informes_recaudacion SET 
                        CI_Usuario = ?,
                        Nombre_Maquina = ?,
                        ID_Comercio = ?,
                        Nombre_Comercio = ?,
                        Direccion_Comercio = ?,
                        Telefono_Comercio = ?,
                        Pago_Ensamblador = ?,
                        Pago_Comprobador = ?,
                        Pago_Mantenimiento = ?,
                        empresa_nombre = ?,
                        empresa_descripcion = ?
                    WHERE ID_Recaudacion = ?";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                "ssssssdddsss",
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
                $data['empresa_descripcion'],
                $data['ID_Recaudacion']
            );
        } else {
            // Insertar nuevo informe
            $sql = "INSERT INTO informes_recaudacion (
                        ID_Informe,
                        ID_Recaudacion,
                        CI_Usuario,
                        Nombre_Maquina,
                        ID_Comercio,
                        Nombre_Comercio,
                        Direccion_Comercio,
                        Telefono_Comercio,
                        Pago_Ensamblador,
                        Pago_Comprobador,
                        Pago_Mantenimiento,
                        empresa_nombre,
                        empresa_descripcion
                    ) VALUES (UUID(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                "sssssssdddss",
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
        }

        if ($stmt->execute()) {
            // Obtener el ID del informe insertado/actualizado
            if ($checkResult->num_rows > 0) {
                // Si era actualización, obtener el ID existente
                $selectSql = "SELECT ID_Informe FROM informes_recaudacion WHERE ID_Recaudacion = ?";
                $selectStmt = $conn->prepare($selectSql);
                $selectStmt->bind_param("s", $data['ID_Recaudacion']);
                $selectStmt->execute();
                $selectResult = $selectStmt->get_result();
                $row = $selectResult->fetch_assoc();
                return $row['ID_Informe'];
            } else {
                // Si era inserción, obtener el último ID insertado
                $idInforme = $conn->insert_id;
                if (!$idInforme) {
                    $result = $conn->query("SELECT LAST_INSERT_ID() as id");
                    $row = $result->fetch_assoc();
                    $idInforme = $row['id'];
                }
                return $idInforme;
            }
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
        $sql = "INSERT INTO informe_detalle (ID_Informe_Detalle, ID_Informe, ID_Componente) 
                VALUES (UUID(), ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $idInforme, $idComponente);
        
        return $stmt->execute();

    } catch (Exception $e) {
        error_log("Error en guardarDetalleComponente: " . $e->getMessage());
        return false;
    }
}
public function obtenerInformePrincipal($idRecaudacion) {
    $conn = $this->db->getConnection();
    
    $sql = "SELECT * FROM informes_recaudacion WHERE ID_Recaudacion = ?";
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
            FROM informe_detalle id
            JOIN componente c ON id.ID_Componente = c.ID_Componente
            WHERE id.ID_Informe = ?";
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
    
    $sql = "SELECT 
                r.*, 
                m.Nombre_Maquina,
                c.Nombre as Nombre_Comercio,
                c.Tipo as Tipo_Comercio,
                u.nombre as nombre_usuario, 
                u.apellido as apellido_usuario,
                (SELECT ID_Informe FROM informes_recaudacion WHERE ID_Recaudacion = r.ID_Recaudacion LIMIT 1) as ID_Informe
            FROM recaudaciones r
            INNER JOIN MaquinaRecreativa m ON r.ID_Maquina = m.ID_Maquina
            INNER JOIN Comercio c ON m.ID_Comercio = c.ID_Comercio
            INNER JOIN usuario u ON r.ID_Usuario = u.ID_Usuario
            WHERE r.ID_Recaudacion = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $idRecaudacion);
    $stmt->execute();
    
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $row['fecha'] = date('Y-m-d H:i:s', strtotime($row['fecha']));
        return $row;
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