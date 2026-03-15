<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/ComercioModel.php';

class ComponenteModel {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }
public function obtenerComponentes($tipo = null, $limit = 10, $offset = 0) {
    $conn = $this->db->getConnection();
    
    // Consulta para obtener componentes con información de si están en uso
    $sql = "SELECT c.*, 
                   CASE WHEN cu.ID_Componente IS NOT NULL THEN 1 ELSE 0 END as en_uso,
                   cu.ID_Usuario as usuario_uso,
                   cu.ID_Maquina as maquina_uso
            FROM componente c
            LEFT JOIN componente_usuario cu ON c.ID_Componente = cu.ID_Componente AND cu.fecha_liberacion IS NULL
            WHERE 1=1";
    
    $params = [];
    $types = "";

    if ($tipo && $tipo !== 'todos') {
        $sql .= " AND c.tipo = ?";
        $params[] = $tipo;
        $types .= "s";
    }

    $sql .= " GROUP BY c.ID_Componente LIMIT ? OFFSET ?";
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
        $countSql .= " WHERE tipo = ?";
        $countStmt = $conn->prepare($countSql);
        $countStmt->bind_param("s", $tipo);
        $countStmt->execute();
        $countResult = $countStmt->get_result();
    } else {
        $countResult = $conn->query($countSql);
    }
    
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
        $checkSql = "SELECT COUNT(*) as count FROM componente_usuario 
                     WHERE ID_Componente = ? AND fecha_liberacion IS NULL";
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

        // Insertar en componente_usuario
        $insertSql = "INSERT INTO componente_usuario (ID_Registro, ID_Componente, ID_Usuario, ID_Maquina, fecha_asignacion) 
                      VALUES (UUID(), ?, ?, ?, NOW())";
        $insertStmt = $conn->prepare($insertSql);
        $insertStmt->bind_param("sss", $idComponente, $idUsuario, $idMaquina);
        
        if (!$insertStmt->execute()) {
            $conn->rollback();
            return [
                'success' => false,
                'message' => 'Error al asignar el componente: ' . $insertStmt->error
            ];
        }

        // Si hay máquina, registrar en montaje
        if ($idMaquina) {
            // Obtener nombre del componente
            $nombreSql = "SELECT nombre FROM componente WHERE ID_Componente = ?";
            $nombreStmt = $conn->prepare($nombreSql);
            $nombreStmt->bind_param("s", $idComponente);
            $nombreStmt->execute();
            $nombreResult = $nombreStmt->get_result();
            $nombreRow = $nombreResult->fetch_assoc();
            $nombreComponente = $nombreRow ? $nombreRow['nombre'] : 'Componente';

            $montajeSql = "INSERT INTO montaje (ID_Montaje, fecha, ID_Maquina, ID_Componente, ID_Tecnico, detalle) 
                           VALUES (UUID(), NOW(), ?, ?, ?, ?)";
            $montajeStmt = $conn->prepare($montajeSql);
            $detalle = "Componente $nombreComponente asignado";
            $montajeStmt->bind_param("ssss", $idMaquina, $idComponente, $idUsuario, $detalle);
            $montajeStmt->execute();
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
            'message' => 'Error en la base de datos: ' . $e->getMessage()
        ];
    }
}
public function liberarComponente($idComponente, $idUsuario) {
    $conn = $this->db->getConnection();
    
    try {
        $conn->begin_transaction();

        // Verificar que el componente está asignado al usuario
        $checkSql = "SELECT ID_Registro, ID_Maquina FROM componente_usuario 
                     WHERE ID_Componente = ? AND ID_Usuario = ? AND fecha_liberacion IS NULL";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("ss", $idComponente, $idUsuario);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($checkResult->num_rows == 0) {
            $conn->rollback();
            return [
                'success' => false,
                'message' => 'El componente no está asignado a este usuario'
            ];
        }

        $row = $checkResult->fetch_assoc();
        $idRegistro = $row['ID_Registro'];
        $idMaquina = $row['ID_Maquina'];

        // Marcar como liberado
        $updateSql = "UPDATE componente_usuario SET fecha_liberacion = NOW() WHERE ID_Registro = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("s", $idRegistro);
        $updateStmt->execute();

        // Si estaba en una máquina, no eliminamos de montaje (historial)
        // Solo marcamos como liberado en componente_usuario

        $conn->commit();
        return [
            'success' => true,
            'message' => 'Componente liberado correctamente' . ($idMaquina ? ' de la máquina' : '')
        ];

    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error en liberarComponente: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Error en la base de datos: ' . $e->getMessage()
        ];
    }
}
public function obtenerComponentesEnUso($idUsuario, $idMaquina = null) {
    $conn = $this->db->getConnection();
    
    $sql = "SELECT 
                c.ID_Componente,
                c.tipo,
                c.nombre,
                c.precio,
                cu.fecha_asignacion,
                cu.ID_Maquina,
                m.Nombre_Maquina,
                CASE 
                    WHEN cu.ID_Maquina IS NOT NULL THEN 'Asignado permanentemente'
                    ELSE 'En uso temporal'
                END as estado_uso
            FROM componente_usuario cu
            INNER JOIN componente c ON cu.ID_Componente = c.ID_Componente
            LEFT JOIN MaquinaRecreativa m ON cu.ID_Maquina = m.ID_Maquina
            WHERE cu.ID_Usuario = ? AND cu.fecha_liberacion IS NULL";
    
    $params = [$idUsuario];
    $types = "s";

    if ($idMaquina) {
        $sql .= " AND cu.ID_Maquina = ?";
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
        $mensajes = [];

        if ($idPlaca) {
            $updatePlacaSql = "UPDATE componente_usuario 
                               SET fecha_liberacion = NOW() 
                               WHERE ID_Componente = ? AND ID_Usuario = ? AND fecha_liberacion IS NULL";
            $updatePlacaStmt = $conn->prepare($updatePlacaSql);
            $updatePlacaStmt->bind_param("ss", $idPlaca, $idUsuario);
            $updatePlacaStmt->execute();
            $afectadas = $updatePlacaStmt->affected_rows;
            if ($afectadas > 0) {
                $componentesLiberados++;
                $mensajes[] = "Placa liberada";
            }
        }

        if ($idCarcasa) {
            $updateCarcasaSql = "UPDATE componente_usuario 
                                 SET fecha_liberacion = NOW() 
                                 WHERE ID_Componente = ? AND ID_Usuario = ? AND fecha_liberacion IS NULL";
            $updateCarcasaStmt = $conn->prepare($updateCarcasaSql);
            $updateCarcasaStmt->bind_param("ss", $idCarcasa, $idUsuario);
            $updateCarcasaStmt->execute();
            $afectadas = $updateCarcasaStmt->affected_rows;
            if ($afectadas > 0) {
                $componentesLiberados++;
                $mensajes[] = "Carcasa liberada";
            }
        }

        $conn->commit();
        
        if ($componentesLiberados > 0) {
            return [
                'success' => true,
                'message' => implode(', ', $mensajes) . " correctamente"
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
            'message' => 'Error en la base de datos: ' . $e->getMessage()
        ];
    }
}

    public function asignarCarcasa($idComponente, $idUsuario) {
    $conn = $this->db->getConnection();
    
    try {
        $conn->begin_transaction();

        // Verificar si el componente está disponible (no tiene registro activo)
        $checkSql = "SELECT COUNT(*) as count FROM componente_usuario 
                     WHERE ID_Componente = ? AND fecha_liberacion IS NULL";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("s", $idComponente);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        $checkRow = $checkResult->fetch_assoc();

        if ($checkRow['count'] > 0) {
            $conn->rollback();
            return [
                'success' => false,
                'message' => 'La carcasa ya está en uso'
            ];
        }

        // Asignar componente
        $insertSql = "INSERT INTO componente_usuario (ID_Registro, ID_Componente, ID_Usuario, fecha_asignacion) 
                      VALUES (UUID(), ?, ?, NOW())";
        $insertStmt = $conn->prepare($insertSql);
        $insertStmt->bind_param("ss", $idComponente, $idUsuario);
        
        if (!$insertStmt->execute()) {
            $conn->rollback();
            return [
                'success' => false,
                'message' => 'Error al asignar la carcasa: ' . $insertStmt->error
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
            'message' => 'Error en la base de datos: ' . $e->getMessage()
        ];
    }
}
}
?>