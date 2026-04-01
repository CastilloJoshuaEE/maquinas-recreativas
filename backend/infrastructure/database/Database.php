<?php
/**
 * backend/infrastructure/database/Database.php
 */

namespace maquinas_recreativas\Infrastructure\Database;

class Database {
    protected $connection;
    protected $host;
    protected $username;
    protected $password;
    protected $dbname;

    public function __construct() {
        $this->host = DB_HOST;
        $this->username = DB_USER;
        $this->password = DB_PASS;
        $this->dbname = DB_NAME;
        
        $this->connection = new \mysqli($this->host, $this->username, $this->password, $this->dbname);
        
        if ($this->connection->connect_error) {
            die("Connection failed: " . $this->connection->connect_error);
        }

        $this->connection->set_charset("utf8mb4");
    }

    public function getConnection() {
        return $this->connection;
    }

    public function getHost(): string {
        return $this->host;
    }

    public function getUsername(): string {
        return $this->username;
    }

    public function getPassword(): string {
        return $this->password;
    }

    public function getDbName(): string {
        return $this->dbname;
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