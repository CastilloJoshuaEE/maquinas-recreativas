<?php
/**
 * backend/config/database.php
 * maquinas_recreativas - Database Configuration
 * 
 * Configuración y conexión a la base de datos.
 * 
 * @package maquinas_recreativas\Config
 * @author Tu Equipo
 * @version 1.0
 */


class Database {
    private $connection;
    public function __construct(){
        $this->connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if($this->connection->connect_error){
            die("Connection failed:". $this->connection->connect_error);
        }
        $this->connection->set_charset("utf8mb4");
        // Insertar usuarios iniciales si es necesario
        $this->insertarUsuariosInicialesSiNecesario();
    }
    public function insertarUsuariosInicialesSiNecesario(){
        $lockFile = CONFIG_PATH .'/.usuarios_iniciales.lock';
        if(!file_exists($lockFile) && (APP_ENV === 'development' || APP_ENV === 'testing')){
            require_once __DIR__.'/Inserter.php';
            $inserter = new Inserter($this->connection);
            try{
                $inserter->insertarUsuariosIniciales();
                file_put_contents($lockFile, "Usuarios iniciales creados el".date(DATE_FORMAT));
            }catch(Exception $e){
                error_log("Error al insertar usuarios iniciales:".$e->getMessage());
            }
        }
    }
    public function getConnection(){
        return $this->connection;
    }
    public function closeConnection(){
        if($this->connection){
            $this->connection->close();
        }
    }
    public function query($sql){
        $result = $this->connection->query($sql);
        if(!$result){
            error_log("Error en consulta SQL:".$this->connection->error);
            error_log("Consulta:".$sql);
        }
        return $result;
    }
    public function escapeString($string){
        return $this->connection->real_escape_string($string);
    }
    public function getLastInsertId(){
        return $this->connection->insert_id;
    }
    
}
// Detectar entorno y seleccionar base de datos
$isTest = (
    (APP_ENV ??'')==='testing'||
    (defined('TEST_ENVIRONMENT')&& TEST_ENVIRONMENT === true)
);
$dbName = DB_NAME??null;
if($isTest){
    $testDbName=getenv('DB_NAME_TEST')?:(defined('DB_NAME_TEST')?DB_NAME_TEST:null);
    $dbName = $testDbName?:$dbName;
}
if (!$dbName) {
    die("No se ha definido DB_NAME en el .env");
}

// Definir constantes de base de datos si no están definidas
if (!defined('DB_HOST')) define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
if (!defined('DB_USER')) define('DB_USER', getenv('DB_USER') ?: 'root');
if (!defined('DB_PASS')) define('DB_PASS', getenv('DB_PASS') ?: '');
if (!defined('DB_NAME')) define('DB_NAME', $dbName);