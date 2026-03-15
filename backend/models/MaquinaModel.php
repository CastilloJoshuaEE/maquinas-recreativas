<?php
require_once __DIR__ . '/../config/database.php';

class MaquinaModel {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

 

    private function verificarTecnico($idTecnico) {
        $conn = $this->db->getConnection();
        $sql = "SELECT COUNT(*) as count FROM Tecnico WHERE ID_Tecnico = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $idTecnico);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        if ($row['count'] == 0) {
            throw new Exception("El técnico con ID $idTecnico no existe o no es un técnico válido");
        }
    }

    public function registrarMaquina($nombre, $tipo, $idEnsamblador, $idComprobador, $idComercio) {
        $conn = $this->db->getConnection();
        
        try {
            $conn->begin_transaction();

            $this->verificarTecnico($idEnsamblador);
            $this->verificarTecnico($idComprobador);
            
            
            // CORRECCIÓN: Usar MaquinaRecreativa y Nombre_Maquina
            $sql = "INSERT INTO MaquinaRecreativa ( Nombre_Maquina, Tipo, Fecha_Registro, Estado, Etapa, ID_Comercio, ID_Tecnico_Ensamblador, ID_Tecnico_Comprobador) 
                    VALUES ( ?, ?, CURDATE(), 'Ensamblandose', 'Montaje', ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssss",  $nombre, $tipo, $idComercio, $idEnsamblador, $idComprobador);

            if (!$stmt->execute()) {
                throw new Exception("Error al registrar máquina: " . $stmt->error);
            }

            $conn->commit();
            return true;

        } catch (Exception $e) {
            $conn->rollback();
            error_log("Error en registrarMaquina: " . $e->getMessage());
            throw $e;
        }
    }
public function generarPlaca($idTecnico) {
    $conn = $this->db->getConnection();
    
    try {
        // Verificar que el técnico existe
        $checkSql = "SELECT COUNT(*) as count FROM usuario WHERE ID_Usuario = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("s", $idTecnico);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        $row = $result->fetch_assoc();
        
        if ($row['count'] == 0) {
            throw new Exception("El técnico con ID $idTecnico no existe");
        }

        // Generar número de placa con formato PL + año + secuencia
        $anio = date('y');
        $prefijo = "PL{$anio}";
        
        // Obtener el último número de placa
        $seqSql = "SELECT MAX(CAST(SUBSTRING(nombre, 5) AS UNSIGNED)) as max_seq 
                   FROM componente 
                   WHERE nombre LIKE ? AND tipo = 'Logistico'";
        $seqStmt = $conn->prepare($seqSql);
        $like = $prefijo . '%';
        $seqStmt->bind_param("s", $like);
        $seqStmt->execute();
        $seqResult = $seqStmt->get_result();
        $seqRow = $seqResult->fetch_assoc();
        
        $secuencia = ($seqRow['max_seq'] ?? 0) + 1;
        $numeroPlaca = $prefijo . str_pad($secuencia, 3, '0', STR_PAD_LEFT);
        
        // Insertar el componente
        
        // Usar UUID() de MySQL
        $sql = "INSERT INTO componente ( tipo, nombre, precio) 
                VALUES ('Logistico', ?, 120.00)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $numeroPlaca);
        
        if (!$stmt->execute()) {
            throw new Exception("Error al insertar componente: " . $stmt->error);
        }
        
        // Obtener el ID generado
        $idComponente = $conn->insert_id;
        if (!$idComponente) {
            // Si insert_id no funciona, obtener el último insertado
            $lastSql = "SELECT ID_Componente FROM componente WHERE nombre = ? ORDER BY ID_Componente DESC LIMIT 1";
            $lastStmt = $conn->prepare($lastSql);
            $lastStmt->bind_param("s", $numeroPlaca);
            $lastStmt->execute();
            $lastResult = $lastStmt->get_result();
            $lastRow = $lastResult->fetch_assoc();
            $idComponente = $lastRow['ID_Componente'];
        }
        
        // Registrar en componente_usuario
        $usoSql = "INSERT INTO componente_usuario (ID_Registro, ID_Componente, ID_Usuario, fecha_asignacion) 
                   VALUES (UUID(), ?, ?, NOW())";
        $usoStmt = $conn->prepare($usoSql);
        $usoStmt->bind_param("ss", $idComponente, $idTecnico);
        
        if (!$usoStmt->execute()) {
            throw new Exception("Error al registrar uso: " . $usoStmt->error);
        }
        
        return [
            'success' => true,
            'placa' => $numeroPlaca,
            'id_componente' => $idComponente
        ];

    } catch (Exception $e) {
        error_log("Error en generarPlaca: " . $e->getMessage());
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}
    public function registrarMontajeComponente($idMaquina, $idComponente, $idTecnico, $detalle = '') {
        $conn = $this->db->getConnection();
        
        try {
            
            $sql = "INSERT INTO montaje (fecha, ID_Maquina, ID_Componente, ID_Tecnico, detalle) 
                    VALUES ( NOW(), ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssss", $idMaquina, $idComponente, $idTecnico, $detalle);
            
            if (!$stmt->execute()) {
                error_log("Error al registrar montaje: " . $stmt->error);
                return false;
            }
            
            return true;

        } catch (Exception $e) {
            error_log("Excepción al registrar montaje: " . $e->getMessage());
            return false;
        }
    }

    public function actualizarEstadoMaquina($idMaquina, $estado, $etapa = null) {
        $conn = $this->db->getConnection();
        
        $sql = "UPDATE MaquinaRecreativa SET estado = ?";
        $params = [$estado];
        $types = "s";

        if ($etapa !== null) {
            $sql .= ", etapa = ?";
            $params[] = $etapa;
            $types .= "s";
        }

        $sql .= " WHERE ID_Maquina = ?";
        $params[] = $idMaquina;
        $types .= "s";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        
        return $stmt->execute();
    }

public function asignarTecnicoMantenimiento($idMaquina, $idTecnico) {
    $conn = $this->db->getConnection();
    
    // CORREGIDO: Usar ID_Tecnico_Mantenimiento en lugar de ID_Mantenimiento
    $sql = "UPDATE MaquinaRecreativa SET ID_Tecnico_Mantenimiento = ? WHERE ID_Maquina = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $idTecnico, $idMaquina);
    
    return $stmt->execute();
}
public function obtenerMaquinasPorTecnicoEnsamblador($idTecnico) {
    $conn = $this->db->getConnection();
    
    $sql = "SELECT m.*, 
                   c.Nombre as NombreComercio,
                   c.Direccion as DireccionComercio
            FROM MaquinaRecreativa m
            LEFT JOIN Comercio c ON m.ID_Comercio = c.ID_Comercio
            WHERE m.ID_Tecnico_Ensamblador = ? AND (m.Estado = 'Ensamblandose' OR m.Estado = 'Reensamblandose')
            ORDER BY m.Fecha_Registro DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $idTecnico);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $maquinas = [];
    
    while ($row = $result->fetch_assoc()) {
        $maquinas[] = $row;
    }
    
    return $maquinas;
}
public function obtenerMaquinasPorTecnicoComprobador($idTecnico) {
    $conn = $this->db->getConnection();
    
    $sql = "SELECT m.*, 
                   c.Nombre as NombreComercio,
                   c.Direccion as DireccionComercio
            FROM MaquinaRecreativa m
            LEFT JOIN Comercio c ON m.ID_Comercio = c.ID_Comercio
            WHERE m.ID_Tecnico_Comprobador = ? AND m.Estado = 'Comprobandose'
            ORDER BY m.Fecha_Registro DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $idTecnico);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $maquinas = [];
    
    while ($row = $result->fetch_assoc()) {
        $maquinas[] = $row;
    }
    
    return $maquinas;
}
// En MaquinaModel.php - Verificar que también use el nombre correcto
public function obtenerMaquinasPorTecnicoMantenimiento($idTecnico) {
    $conn = $this->db->getConnection();
    
    // CORREGIDO: Usar ID_Tecnico_Mantenimiento
    $sql = "SELECT m.*, 
                   c.Nombre as NombreComercio,
                   c.Direccion as DireccionComercio
            FROM MaquinaRecreativa m
            LEFT JOIN Comercio c ON m.ID_Comercio = c.ID_Comercio
            WHERE m.ID_Tecnico_Mantenimiento = ? AND m.Estado = 'No operativa'
            ORDER BY m.Fecha_Registro DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $idTecnico);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $maquinas = [];
    
    while ($row = $result->fetch_assoc()) {
        $maquinas[] = $row;
    }
    
    return $maquinas;
}
    public function obtenerMaquinasPorEstado($estado) {
    $conn = $this->db->getConnection();
    
    $sql = "SELECT m.*, c.nombre as nombre_comercio 
            FROM MaquinaRecreativa m
            LEFT JOIN comercio c ON m.ID_Comercio = c.ID_Comercio
            WHERE m.estado = ?
            ORDER BY m.Fecha_Registro DESC"; // Cambiar aquí también
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $estado);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $maquinas = [];
    
    while ($row = $result->fetch_assoc()) {
        $maquinas[] = $row;
    }
    
    return $maquinas;
}

    public function obtenerMaquinasPorEtapa($etapa) {
    $conn = $this->db->getConnection();
    
    $sql = "SELECT m.*, c.nombre as nombre_comercio 
            FROM MaquinaRecreativa m
            LEFT JOIN comercio c ON m.ID_Comercio = c.ID_Comercio
            WHERE m.etapa = ?
            ORDER BY m.Fecha_Registro DESC"; // Cambiar fecha_creacion por Fecha_Registro
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $etapa);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $maquinas = [];
    
    while ($row = $result->fetch_assoc()) {
        $maquinas[] = $row;
    }
    
    return $maquinas;
}

public function obtenerMaquinaPorId($id) {
    $conn = $this->db->getConnection();
    
    $sql = "SELECT m.*, 
                   c.Nombre as nombre_comercio,
                   c.Direccion as direccion_comercio,
                   c.Telefono as telefono_comercio,
                   e.nombre as nombre_ensamblador, e.apellido as apellido_ensamblador,
                   comp.nombre as nombre_comprobador, comp.apellido as apellido_comprobador,
                   mant.nombre as nombre_mantenimiento, mant.apellido as apellido_mantenimiento
            FROM MaquinaRecreativa m
            LEFT JOIN Comercio c ON m.ID_Comercio = c.ID_Comercio
            LEFT JOIN usuario e ON m.ID_Tecnico_Ensamblador = e.ID_Usuario
            LEFT JOIN usuario comp ON m.ID_Tecnico_Comprobador = comp.ID_Usuario
            LEFT JOIN usuario mant ON m.ID_Tecnico_Mantenimiento = mant.ID_Usuario
            WHERE m.ID_Maquina = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $id);
    $stmt->execute();
    
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return false;
}
    public function obtenerMaquinasOperativasPorComercio($idComercio) {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT m.* 
                FROM MaquinaRecreativa m
                WHERE m.ID_Comercio = ? 
                  AND m.estado = 'Operativa'
                ORDER BY m.Nombre_Maquina ASC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $idComercio);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $maquinas = [];
        
        while ($row = $result->fetch_assoc()) {
            $maquinas[] = $row;
        }
        
        return $maquinas;
    }
// En MaquinaModel.php - CORREGIR método obtenerMaquinasPorEtapaYEstado
public function obtenerMaquinasPorEtapaYEstado($etapa, $estado) {
    $conn = $this->db->getConnection();
    
    $sql = "SELECT 
                m.*, 
                c.Nombre as NombreComercio,
                c.Direccion as DireccionComercio,
                c.Telefono as TelefonoComercio,
                c.Tipo as TipoComercio
            FROM MaquinaRecreativa m
            LEFT JOIN Comercio c ON m.ID_Comercio = c.ID_Comercio
            WHERE m.Etapa = ? AND m.Estado = ?
            ORDER BY m.Fecha_Registro DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $etapa, $estado);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $maquinas = [];
    
    while ($row = $result->fetch_assoc()) {
        $maquinas[] = $row;
    }
    
    return $maquinas;
}

    public function obtenerComponentesMontaje($idMaquina) {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT c.*, m.fecha as fecha_montaje, m.detalle, 
                       u.nombre as nombre_tecnico, u.apellido as apellido_tecnico
                FROM montaje m
                JOIN componente c ON m.ID_Componente = c.ID_Componente
                JOIN usuario u ON m.ID_Tecnico = u.ID_Usuario
                WHERE m.ID_Maquina = ?
                ORDER BY m.fecha DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $idMaquina);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $componentes = [];
        
        while ($row = $result->fetch_assoc()) {
            $componentes[] = $row;
        }
        
        return $componentes;
    }

    public function obtenerComponentesPorMaquina($idMaquina) {
        return $this->obtenerComponentesMontaje($idMaquina);
    }

    public function obtenerComponentesMantenimiento($idMaquina) {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT c.* 
                FROM componente c
                WHERE c.ID_Maquina_Actual = ? AND c.estado = 'En mantenimiento'
                ORDER BY c.fecha_creacion DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $idMaquina);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $componentes = [];
        
        while ($row = $result->fetch_assoc()) {
            $componentes[] = $row;
        }
        
        return $componentes;
    }

    public function insertarMontaje($params) {
        $conn = $this->db->getConnection();
        
        try {
            
            $sql = "INSERT INTO montaje (ID_Maquina, ID_Componente, ID_Tecnico, detalle, fecha) 
                    VALUES ( ?, ?, ?, ?, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                "ssss",
                $params['ID_Maquina'],
                $params['ID_Componente'],
                $params['ID_Tecnico'],
                $params['detalle']
            );

            if ($stmt->execute()) {
                return true;
            }
            
            return false;

        } catch (Exception $e) {
            error_log("Error en insertarMontaje: " . $e->getMessage());
            return false;
        }
    }
}
?>