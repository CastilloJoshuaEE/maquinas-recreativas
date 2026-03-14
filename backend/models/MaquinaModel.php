<?php
require_once __DIR__ . '/../config/database.php';

class MaquinaModel {
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
            
            $idMaquina = $this->generateUUID($conn);
            
            // CORRECCIÓN: Usar MaquinaRecreativa y Nombre_Maquina
            $sql = "INSERT INTO MaquinaRecreativa (ID_Maquina, Nombre_Maquina, Tipo, Fecha_Registro, Estado, Etapa, ID_Comercio, ID_Tecnico_Ensamblador, ID_Tecnico_Comprobador) 
                    VALUES (?, ?, ?, CURDATE(), 'Ensamblandose', 'Montaje', ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssss", $idMaquina, $nombre, $tipo, $idComercio, $idEnsamblador, $idComprobador);

            if (!$stmt->execute()) {
                throw new Exception("Error al registrar máquina: " . $stmt->error);
            }

            $conn->commit();
            return $idMaquina;

        } catch (Exception $e) {
            $conn->rollback();
            error_log("Error en registrarMaquina: " . $e->getMessage());
            throw $e;
        }
    }

    public function generarPlaca($idTecnico) {
        $conn = $this->db->getConnection();
        
        try {
            $checkSql = "SELECT COUNT(*) as count FROM usuario WHERE ID_Usuario = ?";
            $checkStmt = $conn->prepare($checkSql);
            $checkStmt->bind_param("s", $idTecnico);
            $checkStmt->execute();
            $result = $checkStmt->get_result();
            $row = $result->fetch_assoc();
            
            if ($row['count'] == 0) {
                throw new Exception("El técnico con ID $idTecnico no existe");
            }

            // Generar número de placa aleatorio
            $numeroPlaca = 'PLACA-' . strtoupper(substr(md5(uniqid()), 0, 8));
            
            // Crear componente de placa
            $idComponente = $this->generateUUID($conn);
            $sql = "INSERT INTO componente (ID_Componente, tipo, numero_placa, fecha_creacion, ID_Tecnico_Creador) 
                    VALUES (?, 'placa', ?, NOW(), ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $idComponente, $numeroPlaca, $idTecnico);
            
            if ($stmt->execute()) {
                return [
                    'placa' => $numeroPlaca,
                    'id_componente' => $idComponente
                ];
            }
            
            return null;

        } catch (Exception $e) {
            error_log("Error en generarPlaca: " . $e->getMessage());
            return null;
        }
    }

    public function registrarMontajeComponente($idMaquina, $idComponente, $idTecnico, $detalle = '') {
        $conn = $this->db->getConnection();
        
        try {
            $idMontaje = $this->generateUUID($conn);
            
            $sql = "INSERT INTO montaje (ID_Montaje, fecha, ID_Maquina, ID_Componente, ID_Tecnico, detalle) 
                    VALUES (?, NOW(), ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssss", $idMontaje, $idMaquina, $idComponente, $idTecnico, $detalle);
            
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
        
        $sql = "UPDATE MaquinaRecreativa SET ID_Mantenimiento = ? WHERE ID_Maquina = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $idTecnico, $idMaquina);
        
        return $stmt->execute();
    }

    public function obtenerMaquinasPorTecnicoEnsamblador($idTecnico) {
        $conn = $this->db->getConnection();
        
        // CORRECCIÓN: Usar MaquinaRecreativa y Comercio
        $sql = "SELECT m.*, c.Nombre as nombre_comercio 
                FROM MaquinaRecreativa m
                LEFT JOIN Comercio c ON m.ID_Comercio = c.ID_Comercio
                WHERE m.ID_Tecnico_Ensamblador = ?
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
        
        $sql = "SELECT m.*, c.Nombre as nombre_comercio 
                FROM MaquinaRecreativa m
                LEFT JOIN Comercio c ON m.ID_Comercio = c.ID_Comercio
                WHERE m.ID_Tecnico_Comprobador = ?
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

    public function obtenerMaquinasPorTecnicoMantenimiento($idTecnico) {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT m.*, c.Nombre as nombre_comercio 
                FROM MaquinaRecreativa m
                LEFT JOIN Comercio c ON m.ID_Comercio = c.ID_Comercio
                WHERE m.ID_Tecnico_Mantenimiento = ?
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
                ORDER BY m.fecha_creacion DESC";
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
                ORDER BY m.fecha_creacion DESC";
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
                ORDER BY m.nombre ASC";
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

    public function obtenerMaquinasPorEtapaYEstado($etapa, $estado) {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT m.*, c.nombre as nombre_comercio 
                FROM MaquinaRecreativa m
                LEFT JOIN comercio c ON m.ID_Comercio = c.ID_Comercio
                WHERE m.etapa = ? AND m.estado = ?
                ORDER BY m.fecha_creacion DESC";
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
            $idMontaje = $this->generateUUID($conn);
            
            $sql = "INSERT INTO montaje (ID_Montaje, ID_Maquina, ID_Componente, ID_Tecnico, detalle, fecha) 
                    VALUES (?, ?, ?, ?, ?, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                "sssss",
                $idMontaje,
                $params['ID_Maquina'],
                $params['ID_Componente'],
                $params['ID_Tecnico'],
                $params['detalle']
            );

            if ($stmt->execute()) {
                return $idMontaje;
            }
            
            return false;

        } catch (Exception $e) {
            error_log("Error en insertarMontaje: " . $e->getMessage());
            return false;
        }
    }
}
?>