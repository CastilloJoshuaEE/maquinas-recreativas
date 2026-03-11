<?php
require_once __DIR__.'/../config/database.php'; // Usa la configuración real.
class TestDatabase extends Database {
    protected $host = 'localhost';
    protected $db_name;
    protected $username = 'root';
    protected $password = '';
    public $conn;

    public function __construct() {
        $this->db_name = 'prueba_bd_recrea_sys_'; 
        $this->conn = new mysqli($this->host, $this->username, $this->password);
        $this->conn->query("CREATE DATABASE IF NOT EXISTS {$this->db_name}");
        $this->conn->select_db($this->db_name);
    }

    public function getConnection() {
        return $this->conn;
    }

    public function cleanUp() {
        $this->conn->query("DROP DATABASE IF EXISTS {$this->db_name}");
        $this->conn->close();
    }
}
