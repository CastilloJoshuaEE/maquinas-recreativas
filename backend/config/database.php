<?php
/**
 * backend/config/database.php
 */

namespace maquinas_recreativas\Config;

use maquinas_recreativas\Infrastructure\Database\Inserter;

class Database {
    private $connection;
    
    public function __construct() {
        $this->connection = new \mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($this->connection->connect_error) {
            die("Connection failed: " . $this->connection->connect_error);
        }
        $this->connection->set_charset("utf8mb4");
        $this->insertarUsuariosInicialesSiNecesario();
    }
    
    private function insertarUsuariosInicialesSiNecesario() {
        $lockFile = CONFIG_PATH . '/.usuarios_iniciales.lock';
        if (!file_exists($lockFile) && (APP_ENV === 'development' || APP_ENV === 'testing')) {
            $inserter = new Inserter($this->connection);
            try {
                $inserter->insertarUsuariosIniciales();
                file_put_contents($lockFile, "Usuarios iniciales creados el " . date("Y-m-d H:i:s"));
            } catch (\Exception $e) {
                error_log("Error al insertar usuarios iniciales: " . $e->getMessage());
            }
        }
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    public function closeConnection() {
        if ($this->connection) {
            $this->connection->close();
        }
    }
    
    public function query($sql) {
        $result = $this->connection->query($sql);
        if (!$result) {
            error_log("Error en consulta SQL: " . $this->connection->error);
            error_log("Consulta: " . $sql);
        }
        return $result;
    }
    
    public function escapeString($string) {
        return $this->connection->real_escape_string($string);
    }
    
    public function getLastInsertId() {
        return $this->connection->insert_id;
    }
}

// Definir constantes de base de datos si no están definidas
if (!defined('DB_HOST')) {
    $isTest = (
        (APP_ENV ?? '') === 'testing' ||
        (defined('TEST_ENVIRONMENT') && TEST_ENVIRONMENT === true)
    );
    
    $dbName = DB_NAME ?? null;
    if ($isTest) {
        $testDbName = getenv('DB_NAME_TEST') ?: (defined('DB_NAME_TEST') ? DB_NAME_TEST : null);
        $dbName = $testDbName ?: $dbName;
    }
    
    if (!$dbName) {
        die("No se ha definido DB_NAME en el .env");
    }
    
    define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
    define('DB_USER', getenv('DB_USER') ?: 'root');
    define('DB_PASS', getenv('DB_PASS') ?: '');
    define('DB_NAME', $dbName);
}