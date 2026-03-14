<?php
require_once __DIR__ . '/../config/database.php';

class ComercioModel {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

  

    public function registrarComercio($nombre, $tipo, $direccion, $telefono) {
        $conn = $this->db->getConnection();
        
        try {
            
            $sql = "INSERT INTO comercio (nombre, tipo, direccion, telefono, Cantidad_Maquinas) 
                    VALUES ( ?, ?, ?, ?, 0)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssss", $nombre, $tipo, $direccion, $telefono);
            
            if ($stmt->execute()) {
                return true;
            }
            return false;

        } catch (Exception $e) {
            error_log("Error en registrarComercio: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerComercios() {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT * FROM comercio ORDER BY nombre ASC";
        $result = $conn->query($sql);
        $comercios = [];
        
        while ($row = $result->fetch_assoc()) {
            $comercios[] = $row;
        }
        
        return $comercios;
    }
    
    public function obtenerComercioPorId($id) {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT * FROM comercio WHERE ID_Comercio = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        return false;
    }

    public function incrementarMaquinasComercio($idComercio) {
        $conn = $this->db->getConnection();
        
        $sql = "UPDATE comercio SET Cantidad_Maquinas= Cantidad_Maquinas+ 1 WHERE ID_Comercio = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $idComercio);
        
        return $stmt->execute();
    }
}
?>