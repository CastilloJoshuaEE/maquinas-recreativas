<?php
$envPath = __DIR__ . '/../.env';
// backend/infrastructure/database/Database.php
if (!file_exists($envPath)) {
    die("Archivo .env no encontrado");
}
$env = parse_ini_file($envPath);

// =============================================
// DETECTAR ENTORNO (LOCAL / TEST / PRODUCCIÓN)
// =============================================
$isTest = (
    ($env['APP_ENV'] ?? '') === 'testing' ||
    (defined('TEST_ENVIRONMENT') && TEST_ENVIRONMENT === true)
);

// =============================================
// SELECCIONAR BASE DE DATOS
// =============================================
$dbName = $env['DB_NAME'] ?? null;

if ($isTest) {
    $dbName = $env['DB_NAME_TEST'] ?? $dbName;
}

if (!$dbName) {
    die("No se ha definido DB_NAME en el .env");
}

// =============================================
// DEFINIR CONSTANTES
// =============================================
define('DB_HOST', $env['DB_HOST']);
define('DB_USER', $env['DB_USER']);
define('DB_PASS', $env['DB_PASS']);
define('DB_NAME', $dbName);

require_once __DIR__ . '/../helper/CifradoHelper.php';
require_once __DIR__ . '/Inserter.php'; // Asegúrate de que la ruta sea correcta

class Database {
    private $connection;

    public function __construct() {
        $this->connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($this->connection->connect_error) {
            die("Connection failed: " . $this->connection->connect_error);
        }

        $this->connection->set_charset("utf8mb4");
// Si falla el registro automático de usuarios iniciales puede hacerlo manualmente en su phpmyadmin o mysql workbench en SQL
// En el caso de que ya haya generado el archivo usuarios_iniciales.lock anteriormente, proceda a eliminarlo manualmente de  la carpeta config y levante de nuevo el servidor backend y frontend para que el sistema lo haga automáticamente
/* SOLO EN ENTORNOS DE PRUEBAS, DESCARTAR USUARIOS INICIALES
//$this->insertarUsuariosIniciales();

    */
    }

    private function insertarUsuariosIniciales() {
        $lockFile = __DIR__ . '/.usuarios_iniciales.lock';
        
        // Verificar si ya se ejecutó
        if (!file_exists($lockFile)) {
            $inserter = new Inserter($this->connection);
            
            try {
                $inserter->insertarUsuariosIniciales();
                file_put_contents($lockFile, "Usuarios iniciales creados el " . date("Y-m-d H:i:s"));
            } catch (Exception $e) {
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
?>