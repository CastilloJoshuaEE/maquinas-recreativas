<?php
require_once __DIR__.'/../config/database.php'; // Usa la configuración real.

class TestDatabase extends Database {
    // Configuración especial para pruebas:
    protected $host = 'localhost';
    protected $db_name = 'prueba_bd_recrea_sys'; // Base de datos temporal.
    protected $username = 'root';
    protected $password = '';
    public $conn;

    public function __construct() {
        // Conecta y crea la BD temporal:
        $this->conn = new mysqli($this->host, $this->username, $this->password);
        $this->conn->query("CREATE DATABASE IF NOT EXISTS $this->db_name");
        $this->conn->select_db($this->db_name);
    }

    public function getConnection() {
        return $this->conn; // Devuelve la conexión para usarla en pruebas.
    }

    public function cleanUp() {
        // Limpia después de las pruebas:
        $this->conn->query("DROP DATABASE IF EXISTS $this->db_name");
        $this->conn->close();
    }
}