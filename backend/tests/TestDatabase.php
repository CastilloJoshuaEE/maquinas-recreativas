<?php
/**
 * Base de datos para pruebas unitarias
 * 
 * @package maquinas_recreativas\Tests
 */

namespace maquinas_recreativas\Tests;

use maquinas_recreativas\Infrastructure\Database\Database;
use maquinas_recreativas\Infrastructure\Security\CifradoHelper;

class TestDatabase extends Database
{
    private static ?TestDatabase $instance = null;
    private string $testDbName;
    
    private function __construct()
    {
        $this->testDbName = getenv('DB_NAME_TEST') ?: 'test_bd_recrea_sys';
        $this->host = getenv('DB_HOST') ?: 'localhost';
        $this->username = getenv('DB_USER') ?: 'root';
        $this->password = getenv('DB_PASS') ?: '';
        
        $this->connection = new \mysqli($this->host, $this->username, $this->password);
        
        if ($this->connection->connect_error) {
            die("Connection failed: " . $this->connection->connect_error);
        }
        
        $this->createTestDatabase();
        $this->connection->select_db($this->testDbName);
        $this->createTables();
        $this->insertInitialData();
    }
    
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function createTestDatabase(): void
    {
        $this->connection->query("CREATE DATABASE IF NOT EXISTS {$this->testDbName}");
        $this->connection->query("USE {$this->testDbName}");
    }
    
    private function createTables(): void
    {
        $this->connection->query("SET FOREIGN_KEY_CHECKS = 0");
        
        // Tabla: usuario
        $this->connection->query("CREATE TABLE IF NOT EXISTS usuario (
            ID_Usuario CHAR(36) PRIMARY KEY,
            ci VARCHAR(255) NOT NULL,
            nombre VARCHAR(50) NOT NULL,
            apellido VARCHAR(50) NOT NULL,
            email VARCHAR(255) NOT NULL,
            tipo ENUM('Administrador', 'Logistica', 'Tecnico', 'Contabilidad', 'Usuario') NOT NULL,
            usuario_asignado VARCHAR(25) NOT NULL,
            contrasena VARCHAR(255) NOT NULL,
            estado ENUM('Activo', 'Inactivo', 'Suspendido', 'Pendiente_asignacion') DEFAULT 'Activo',
            fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uk_email (email),
            UNIQUE KEY uk_ci (ci),
            UNIQUE KEY uk_usuario_asignado (usuario_asignado)
        )");
        
        // Tabla: Tecnico
        $this->connection->query("CREATE TABLE IF NOT EXISTS Tecnico (
            ID_Tecnico CHAR(36) PRIMARY KEY,
            Especialidad ENUM('Ensamblador', 'Comprobador', 'Mantenimiento') NOT NULL,
            Cantidad_Actividades INT DEFAULT 0 NOT NULL,
            FOREIGN KEY (ID_Tecnico) REFERENCES usuario(ID_Usuario) ON DELETE CASCADE
        )");
        
        // Tabla: Logistica
        $this->connection->query("CREATE TABLE IF NOT EXISTS Logistica (
            ID_Logistica CHAR(36) PRIMARY KEY,
            FOREIGN KEY (ID_Logistica) REFERENCES usuario(ID_Usuario) ON DELETE CASCADE
        )");
        
        // Tabla: Comercio
        $this->connection->query("CREATE TABLE IF NOT EXISTS Comercio (
            ID_Comercio CHAR(36) PRIMARY KEY,
            Nombre VARCHAR(100) NOT NULL UNIQUE,
            Tipo ENUM('Minorista', 'Mayorista') NOT NULL,
            Direccion TEXT NOT NULL,
            Telefono VARCHAR(15) NOT NULL,
            Cantidad_Maquinas INT DEFAULT 0 NOT NULL,
            Fecha_Registro DATE NOT NULL
        )");
        
        // Tabla: MaquinaRecreativa
        $this->connection->query("CREATE TABLE IF NOT EXISTS MaquinaRecreativa (
            ID_Maquina CHAR(36) PRIMARY KEY,
            Nombre_Maquina VARCHAR(100) NOT NULL,
            Tipo VARCHAR(50) NOT NULL,
            Etapa ENUM('Montaje', 'Distribucion', 'Recaudacion') DEFAULT 'Montaje',
            Estado ENUM('Ensamblandose', 'Reensamblandose', 'Comprobandose', 'Distribuyendose', 'Operativa', 'No operativa', 'Retirada') DEFAULT 'Ensamblandose',
            Fecha_Registro DATE NOT NULL,
            ID_Comercio CHAR(36) NOT NULL,
            ID_Tecnico_Ensamblador CHAR(36) NOT NULL,
            ID_Tecnico_Comprobador CHAR(36) NOT NULL,
            ID_Tecnico_Mantenimiento CHAR(36) NULL,
            FOREIGN KEY (ID_Comercio) REFERENCES Comercio(ID_Comercio),
            FOREIGN KEY (ID_Tecnico_Ensamblador) REFERENCES Tecnico(ID_Tecnico),
            FOREIGN KEY (ID_Tecnico_Comprobador) REFERENCES Tecnico(ID_Tecnico),
            FOREIGN KEY (ID_Tecnico_Mantenimiento) REFERENCES Tecnico(ID_Tecnico)
        )");
        
        // Tabla: componente
        $this->connection->query("CREATE TABLE IF NOT EXISTS componente (
            ID_Componente CHAR(36) PRIMARY KEY,
            tipo ENUM('Logistico', 'Electronico', 'Estructural', 'Accesorio') NOT NULL,
            nombre VARCHAR(50) NOT NULL,
            precio DECIMAL(10,2) DEFAULT 0,
            fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP
        )");
        
        // Tabla: componente_usuario
        $this->connection->query("CREATE TABLE IF NOT EXISTS componente_usuario (
            ID_Registro CHAR(36) PRIMARY KEY,
            ID_Componente CHAR(36) NOT NULL,
            ID_Usuario CHAR(36) NOT NULL,
            ID_Maquina CHAR(36) NULL,
            fecha_asignacion DATETIME DEFAULT CURRENT_TIMESTAMP,
            fecha_liberacion DATETIME NULL,
            FOREIGN KEY (ID_Componente) REFERENCES componente(ID_Componente),
            FOREIGN KEY (ID_Usuario) REFERENCES usuario(ID_Usuario) ON DELETE CASCADE,
            FOREIGN KEY (ID_Maquina) REFERENCES MaquinaRecreativa(ID_Maquina)
        )");
        
        // Tabla: montaje
        $this->connection->query("CREATE TABLE IF NOT EXISTS montaje (
            ID_Montaje CHAR(36) PRIMARY KEY,
            ID_Maquina CHAR(36) NOT NULL,
            ID_Componente CHAR(36) NOT NULL,
            ID_Tecnico CHAR(36) NOT NULL,
            detalle TEXT,
            fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (ID_Maquina) REFERENCES MaquinaRecreativa(ID_Maquina),
            FOREIGN KEY (ID_Componente) REFERENCES componente(ID_Componente),
            FOREIGN KEY (ID_Tecnico) REFERENCES Tecnico(ID_Tecnico)
        )");
        
        // Tabla: reporte
        $this->connection->query("CREATE TABLE IF NOT EXISTS reporte (
            ID_Reporte CHAR(36) PRIMARY KEY,
            ID_Usuario_Emisor CHAR(36) NOT NULL,
            ID_Usuario_Destinatario CHAR(36) NULL,
            descripcion TEXT NOT NULL,
            fecha_hora DATETIME NOT NULL,
            estado ENUM('Pendiente', 'En proceso', 'Resuelto') DEFAULT 'Pendiente',
            FOREIGN KEY (ID_Usuario_Emisor) REFERENCES usuario(ID_Usuario) ON DELETE CASCADE,
            FOREIGN KEY (ID_Usuario_Destinatario) REFERENCES usuario(ID_Usuario) ON DELETE SET NULL
        )");
        
        // Tabla: comentario
        $this->connection->query("CREATE TABLE IF NOT EXISTS comentario (
            ID_Comentario CHAR(36) PRIMARY KEY,
            ID_Reporte CHAR(36) NOT NULL,
            ID_Usuario_Emisor CHAR(36) NOT NULL,
            comentario TEXT NOT NULL,
            fecha_hora DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (ID_Reporte) REFERENCES reporte(ID_Reporte) ON DELETE CASCADE,
            FOREIGN KEY (ID_Usuario_Emisor) REFERENCES usuario(ID_Usuario) ON DELETE CASCADE
        )");
        
        // Tabla: recaudaciones
        $this->connection->query("CREATE TABLE IF NOT EXISTS recaudaciones (
            ID_Recaudacion CHAR(36) PRIMARY KEY,
            ID_Maquina CHAR(36) NOT NULL,
            ID_Usuario CHAR(36) NOT NULL,
            Tipo_Comercio ENUM('Minorista', 'Mayorista') NOT NULL,
            Monto_Total DECIMAL(10,2) NOT NULL,
            Monto_Empresa DECIMAL(10,2),
            Monto_Comercio DECIMAL(10,2),
            Porcentaje_Comercio DECIMAL(5,2),
            detalle TEXT,
            fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (ID_Maquina) REFERENCES MaquinaRecreativa(ID_Maquina),
            FOREIGN KEY (ID_Usuario) REFERENCES usuario(ID_Usuario)
        )");
        
        // Tabla: informes_recaudacion
        $this->connection->query("CREATE TABLE IF NOT EXISTS informes_recaudacion (
            ID_Informe CHAR(36) PRIMARY KEY,
            ID_Recaudacion CHAR(36) NOT NULL,
            CI_Usuario VARCHAR(100) NOT NULL,
            Nombre_Maquina VARCHAR(100) NOT NULL,
            ID_Comercio CHAR(36) NOT NULL,
            Nombre_Comercio VARCHAR(100) NOT NULL,
            Direccion_Comercio TEXT NOT NULL,
            Telefono_Comercio VARCHAR(15) NOT NULL,
            Pago_Ensamblador DECIMAL(10,2) DEFAULT 400.00,
            Pago_Comprobador DECIMAL(10,2) DEFAULT 400.00,
            Pago_Mantenimiento DECIMAL(10,2) DEFAULT 0,
            empresa_nombre VARCHAR(100) DEFAULT 'Recrea Sys S.A.',
            empresa_descripcion VARCHAR(255) DEFAULT 'Empresa especializada en el ciclo de vida de máquinas recreativas',
            FOREIGN KEY (ID_Recaudacion) REFERENCES recaudaciones(ID_Recaudacion),
            FOREIGN KEY (ID_Comercio) REFERENCES Comercio(ID_Comercio)
        )");
        
        // Tabla: informe_detalle
        $this->connection->query("CREATE TABLE IF NOT EXISTS informe_detalle (
            ID_Informe_Detalle CHAR(36) PRIMARY KEY,
            ID_Informe CHAR(36) NOT NULL,
            ID_Componente CHAR(36) NOT NULL,
            FOREIGN KEY (ID_Informe) REFERENCES informes_recaudacion(ID_Informe) ON DELETE CASCADE,
            FOREIGN KEY (ID_Componente) REFERENCES componente(ID_Componente)
        )");
        
        // Tabla: NotificacionMaquinaRecreativa
        $this->connection->query("CREATE TABLE IF NOT EXISTS NotificacionMaquinaRecreativa (
            ID_Notificacion CHAR(36) PRIMARY KEY,
            ID_Remitente CHAR(36) NOT NULL,
            ID_Destinatario CHAR(36) NOT NULL,
            ID_Maquina CHAR(36) NOT NULL,
            Tipo VARCHAR(100) NOT NULL,
            Mensaje TEXT,
            Fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
            Estado ENUM('Leido', 'No leido') DEFAULT 'No leido',
            FOREIGN KEY (ID_Remitente) REFERENCES usuario(ID_Usuario) ON DELETE CASCADE,
            FOREIGN KEY (ID_Destinatario) REFERENCES usuario(ID_Usuario) ON DELETE CASCADE,
            FOREIGN KEY (ID_Maquina) REFERENCES MaquinaRecreativa(ID_Maquina) ON DELETE CASCADE
        )");
        
        // Tabla: informe_distribucion
        $this->connection->query("CREATE TABLE IF NOT EXISTS informe_distribucion (
            ID_Distribucion CHAR(36) PRIMARY KEY,
            ID_Maquina CHAR(36) NOT NULL,
            ID_Usuario_Comprobador CHAR(36) NOT NULL,
            ID_Comercio CHAR(36) NOT NULL,
            fecha_alta DATETIME DEFAULT CURRENT_TIMESTAMP,
            fecha_baja DATETIME NULL,
            estado ENUM('Operativa', 'Retirada', 'No operativa', 'Distribuyendose') DEFAULT 'Distribuyendose',
            FOREIGN KEY (ID_Maquina) REFERENCES MaquinaRecreativa(ID_Maquina),
            FOREIGN KEY (ID_Usuario_Comprobador) REFERENCES usuario(ID_Usuario),
            FOREIGN KEY (ID_Comercio) REFERENCES Comercio(ID_Comercio)
        )");
        
        // Tabla: historial_maquinas
        $this->connection->query("CREATE TABLE IF NOT EXISTS historial_maquinas (
            ID_Historial CHAR(36) PRIMARY KEY,
            ID_Maquina CHAR(36) NOT NULL,
            ID_Usuario CHAR(36) NOT NULL,
            tipo_usuario VARCHAR(50),
            accion VARCHAR(100) NOT NULL,
            descripcion TEXT,
            estado_anterior VARCHAR(50),
            estado_nuevo VARCHAR(50),
            etapa_anterior VARCHAR(50),
            etapa_nueva VARCHAR(50),
            ip_address VARCHAR(45),
            detalles_adicionales TEXT,
            fecha_hora DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (ID_Maquina) REFERENCES MaquinaRecreativa(ID_Maquina),
            FOREIGN KEY (ID_Usuario) REFERENCES usuario(ID_Usuario)
        )");
        
        $this->connection->query("SET FOREIGN_KEY_CHECKS = 1");
    }
    
    private function insertInitialData(): void
    {
        // Insertar usuario administrador por defecto
        $adminId = $this->generateUUID();
        $hashedPassword = password_hash('admin123', PASSWORD_BCRYPT);
        $emailEncrypted = CifradoHelper::encriptar('admin@test.com');
        $ciEncrypted = CifradoHelper::encriptar('1234567890');
        
        $stmt = $this->connection->prepare("INSERT INTO usuario (ID_Usuario, nombre, apellido, ci, email, usuario_asignado, contrasena, tipo, estado) 
            VALUES (?, 'Admin', 'Test', ?, ?, 'admin_test', ?, 'Administrador', 'Activo')");
        $stmt->bind_param('ssss', $adminId, $ciEncrypted, $emailEncrypted, $hashedPassword);
        $stmt->execute();
        $stmt->close();
    }
    
    public function generateUUID(): string
    {
        $result = $this->connection->query("SELECT UUID() as uuid");
        $row = $result->fetch_assoc();
        return $row['uuid'];
    }
    
    public function getConnection(): \mysqli
    {
        return $this->connection;
    }
    
    public function close(): void
    {
        if ($this->connection) {
            $this->connection->close();
        }
    }
    
    public function beginTransaction(): void
    {
        $this->connection->begin_transaction();
    }
    
    public function commit(): void
    {
        $this->connection->commit();
    }
    
    public function rollback(): void
    {
        $this->connection->rollback();
    }
    
    public function cleanDatabase(): void
    {
        $this->connection->query("SET FOREIGN_KEY_CHECKS = 0");
        
        $tables = [
            'comentario', 'notificaciones', 'reporte', 'componente_usuario',
            'montaje', 'componente', 'informe_detalle', 'informes_recaudacion',
            'recaudaciones', 'informe_distribucion', 'NotificacionMaquinaRecreativa',
            'MaquinaRecreativa', 'Comercio', 'inicio_sesion', 'historial_actividades',
            'Logistica', 'Tecnico', 'usuario'
        ];
        
        foreach ($tables as $table) {
            $this->connection->query("TRUNCATE TABLE $table");
        }
        
        $this->connection->query("SET FOREIGN_KEY_CHECKS = 1");
        $this->insertInitialData();
    }
} 