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
    
    /**
     * Constructor privado (Singleton)
     */
    private function __construct()
    {
        $this->testDbName = getenv('DB_NAME_TEST') ?: 'test_bd_recrea_sys';
        $this->host = getenv('DB_HOST') ?: 'localhost';
        $this->username = getenv('DB_USER') ?: 'root';
        $this->password = getenv('DB_PASS') ?: '';
        $this->dbname = $this->testDbName;
        
        // Crear conexión sin seleccionar base de datos primero
        $this->connection = new \mysqli($this->host, $this->username, $this->password);
        
        if ($this->connection->connect_error) {
            die("Connection failed: " . $this->connection->connect_error);
        }
        
        // Crear y seleccionar base de datos de prueba
        $this->createTestDatabase();
        $this->connection->select_db($this->testDbName);
        
        // Crear tablas y datos iniciales
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
    
    /**
     * Crear la base de datos de prueba si no existe
     */
    private function createTestDatabase(): void
    {
        $this->connection->query("CREATE DATABASE IF NOT EXISTS {$this->testDbName}");
        $this->connection->query("USE {$this->testDbName}");
    }
    
    /**
     * Crear todas las tablas necesarias para las pruebas
     * Este SQL debe coincidir exactamente con la estructura de la BD real
     */
    private function createTables(): void
    {
        $this->connection->query("SET FOREIGN_KEY_CHECKS = 0");
        
        // Eliminar todas las tablas en orden inverso a las dependencias
        $tables = [
            'comentario', 'notificaciones', 'reporte', 'componente_usuario',
            'montaje', 'componente', 'informe_detalle', 'informes_recaudacion',
            'recaudaciones', 'informe_distribucion', 'NotificacionMaquinaRecreativa',
            'historial_maquinas', 'MaquinaRecreativa', 'Comercio', 'inicio_sesion',
            'historial_actividades', 'Logistica', 'Tecnico', 'usuario'
        ];
        
        foreach ($tables as $table) {
            $this->connection->query("DROP TABLE IF EXISTS $table");
        }
        
        // Tabla: usuario
        $this->connection->query("CREATE TABLE usuario (
            ID_Usuario CHAR(36) PRIMARY KEY DEFAULT (UUID()),
            ci VARCHAR(100) NOT NULL UNIQUE,
            nombre VARCHAR(50) NOT NULL,
            apellido VARCHAR(50) NOT NULL,
            email VARCHAR(100) NOT NULL UNIQUE,
            tipo ENUM('Administrador', 'Logistica', 'Tecnico', 'Contabilidad', 'Usuario') NOT NULL,
            usuario_asignado VARCHAR(25) NOT NULL UNIQUE DEFAULT 'Aun no tiene',
            contrasena VARCHAR(255) NOT NULL DEFAULT 'Aun no tiene',
            estado ENUM('Pendiente de asignacion', 'Activo', 'Inhabilitado') DEFAULT 'Pendiente de asignacion' NOT NULL,
            fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP
        )");
        
        // Tabla: Tecnico
        $this->connection->query("CREATE TABLE Tecnico (
            ID_Tecnico CHAR(36) PRIMARY KEY,
            Especialidad ENUM('Ensamblador', 'Comprobador', 'Mantenimiento') NOT NULL,
            Cantidad_Actividades INT DEFAULT 0 NOT NULL,
            FOREIGN KEY (ID_Tecnico) REFERENCES usuario(ID_Usuario) ON DELETE CASCADE
        )");
        
        // Tabla: Logistica
        $this->connection->query("CREATE TABLE Logistica (
            ID_Logistica CHAR(36) PRIMARY KEY,
            FOREIGN KEY (ID_Logistica) REFERENCES usuario(ID_Usuario) ON DELETE CASCADE
        )");
        
        // Tabla: historial_actividades
        $this->connection->query("CREATE TABLE historial_actividades (
            ID_Historial_Actividades CHAR(36) PRIMARY KEY DEFAULT (UUID()),
            ID_Usuario CHAR(36) NOT NULL,
            descripcion TEXT NOT NULL,
            fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (ID_Usuario) REFERENCES usuario(ID_Usuario) ON DELETE CASCADE
        )");
        
        // Tabla: inicio_sesion
        $this->connection->query("CREATE TABLE inicio_sesion (
            ID_Inicio_Sesion CHAR(36) PRIMARY KEY DEFAULT (UUID()),
            ID_Usuario CHAR(36) NOT NULL,
            usuario_asignado VARCHAR(100) NOT NULL,
            contrasena VARCHAR(255) NOT NULL,
            fecha_inicio DATETIME DEFAULT CURRENT_TIMESTAMP,
            fecha_ultima_sesion DATETIME NULL,
            FOREIGN KEY (ID_Usuario) REFERENCES usuario(ID_Usuario) ON DELETE CASCADE
        )");
        
        // Tabla: Comercio
        $this->connection->query("CREATE TABLE Comercio (
            ID_Comercio CHAR(36) PRIMARY KEY DEFAULT (UUID()),
            Nombre VARCHAR(100) NOT NULL UNIQUE,
            Tipo ENUM('Minorista', 'Mayorista') NOT NULL,
            Direccion TEXT NOT NULL,
            Telefono VARCHAR(15) NOT NULL UNIQUE,
            Cantidad_Maquinas INT DEFAULT 0 NOT NULL,
            Fecha_Registro DATE NOT NULL
        )");
        
        // Tabla: MaquinaRecreativa
        $this->connection->query("CREATE TABLE MaquinaRecreativa (
            ID_Maquina CHAR(36) PRIMARY KEY DEFAULT (UUID()),
            Nombre_Maquina VARCHAR(100) NOT NULL,
            Tipo VARCHAR(50) NOT NULL,
            Etapa ENUM('Montaje', 'Distribucion', 'Recaudacion') DEFAULT 'Montaje' NOT NULL,
            Estado ENUM('Ensamblandose', 'Comprobandose', 'Reensamblandose', 'Distribuyendose', 'Operativa', 'No operativa', 'Retirada') DEFAULT 'Ensamblandose' NOT NULL,
            Fecha_Registro DATE NOT NULL,
            ID_Tecnico_Ensamblador CHAR(36) NOT NULL,
            ID_Tecnico_Comprobador CHAR(36) NOT NULL,
            ID_Comercio CHAR(36) NOT NULL,
            ID_Tecnico_Mantenimiento CHAR(36),
            FOREIGN KEY (ID_Tecnico_Ensamblador) REFERENCES Tecnico(ID_Tecnico),
            FOREIGN KEY (ID_Tecnico_Comprobador) REFERENCES Tecnico(ID_Tecnico),
            FOREIGN KEY (ID_Comercio) REFERENCES Comercio(ID_Comercio),
            FOREIGN KEY (ID_Tecnico_Mantenimiento) REFERENCES Tecnico(ID_Tecnico)
        )");
        
        // Tabla: componente
        $this->connection->query("CREATE TABLE componente (
            ID_Componente CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    tipo ENUM('Logistico', 'Electronico', 'Estructural', 'Accesorio') NOT NULL,
            nombre VARCHAR(50) NOT NULL,
            precio DECIMAL(10,2) DEFAULT 10.00,
            fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP
        )");
        
        // Tabla: componente_usuario
        $this->connection->query("CREATE TABLE componente_usuario (
            ID_Registro CHAR(36) PRIMARY KEY DEFAULT (UUID()),
            ID_Componente CHAR(36) NOT NULL,
            ID_Usuario CHAR(36) NOT NULL,
            fecha_asignacion DATETIME DEFAULT CURRENT_TIMESTAMP,
            fecha_liberacion DATETIME NULL,
            ID_Maquina CHAR(36) NULL,
            FOREIGN KEY (ID_Componente) REFERENCES componente(ID_Componente),
            FOREIGN KEY (ID_Usuario) REFERENCES usuario(ID_Usuario) ON DELETE CASCADE,
            FOREIGN KEY (ID_Maquina) REFERENCES MaquinaRecreativa(ID_Maquina)
        )");
        
        // Tabla: reporte
        $this->connection->query("CREATE TABLE reporte (
            ID_Reporte CHAR(36) PRIMARY KEY DEFAULT (UUID()),
            ID_Usuario_Emisor CHAR(36) NOT NULL,
            ID_Usuario_Destinatario CHAR(36),
            fecha_hora DATETIME NOT NULL,
            descripcion TEXT NOT NULL,
            estado VARCHAR(15) NOT NULL,
            FOREIGN KEY (ID_Usuario_Emisor) REFERENCES usuario(ID_Usuario) ON DELETE CASCADE,
            FOREIGN KEY (ID_Usuario_Destinatario) REFERENCES usuario(ID_Usuario) ON DELETE SET NULL
        )");
        
        // Tabla: notificaciones
        $this->connection->query("CREATE TABLE notificaciones (
            ID_Notificaciones CHAR(36) PRIMARY KEY DEFAULT (UUID()),
            ID_Reporte CHAR(36) NOT NULL,
            ID_Usuario CHAR(36) NOT NULL,
            fecha_hora DATETIME NOT NULL,
            mensaje TEXT NOT NULL,
            leida BOOLEAN DEFAULT FALSE,
            FOREIGN KEY (ID_Reporte) REFERENCES reporte(ID_Reporte) ON DELETE CASCADE,
            FOREIGN KEY (ID_Usuario) REFERENCES usuario(ID_Usuario) ON DELETE CASCADE
        )");
        
        // Tabla: comentario
        $this->connection->query("CREATE TABLE comentario (
            ID_Comentario CHAR(36) PRIMARY KEY DEFAULT (UUID()),
            ID_Reporte CHAR(36) NOT NULL,
            ID_Usuario_Emisor CHAR(36) NOT NULL, 
            fecha_hora DATETIME NOT NULL,
            comentario TEXT NOT NULL,
            FOREIGN KEY (ID_Reporte) REFERENCES reporte(ID_Reporte) ON DELETE CASCADE,
            FOREIGN KEY (ID_Usuario_Emisor) REFERENCES usuario(ID_Usuario) ON DELETE CASCADE
        )");
        
        // Tabla: NotificacionMaquinaRecreativa
        $this->connection->query("CREATE TABLE NotificacionMaquinaRecreativa (
            ID_Notificacion CHAR(36) PRIMARY KEY DEFAULT (UUID()),
            ID_Remitente CHAR(36) NOT NULL,
            ID_Destinatario CHAR(36) NOT NULL,
            ID_Maquina CHAR(36) NOT NULL,
            Tipo ENUM(
                'Nuevo montaje',
                'Comprobar maquina recreativa',
                'Reensamblar maquina recreativa',
                'Distribuir maquina recreativa',
                'Dar mantenimiento a maquina recreativa',
                'Maquina recreativa retirada',
                'Maquina recreativa reparada'
            ) NOT NULL,
            Mensaje TEXT,
            Fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            Estado ENUM('Leido', 'No leido') DEFAULT 'No leido' NOT NULL,
            FOREIGN KEY (ID_Remitente) REFERENCES usuario(ID_Usuario) ON DELETE CASCADE,
            FOREIGN KEY (ID_Destinatario) REFERENCES usuario(ID_Usuario) ON DELETE CASCADE,
            FOREIGN KEY (ID_Maquina) REFERENCES MaquinaRecreativa(ID_Maquina) ON DELETE CASCADE
        )");
        
        // Tabla: recaudaciones
        $this->connection->query("CREATE TABLE recaudaciones (
            ID_Recaudacion CHAR(36) PRIMARY KEY DEFAULT (UUID()),
            Tipo_Comercio ENUM('Minorista', 'Mayorista') NOT NULL, 
            ID_Maquina CHAR(36) NOT NULL,
            ID_Usuario CHAR(36) NOT NULL,
            Monto_Total DECIMAL(10,2) NOT NULL,
            Monto_Empresa DECIMAL(10,2),
            Monto_Comercio DECIMAL(10,2),
            Porcentaje_Comercio DECIMAL(5,2) DEFAULT 0,
            fecha DATETIME NOT NULL,
            detalle TEXT NOT NULL,
            FOREIGN KEY (ID_Usuario) REFERENCES usuario(ID_Usuario),
            FOREIGN KEY (ID_Maquina) REFERENCES MaquinaRecreativa(ID_Maquina)
        )");
        
        // Tabla: informes_recaudacion
        $this->connection->query("CREATE TABLE informes_recaudacion (
            ID_Informe CHAR(36) PRIMARY KEY DEFAULT (UUID()),
            ID_Recaudacion CHAR(36) NOT NULL,
            CI_Usuario VARCHAR(100) NOT NULL,
            Nombre_Maquina VARCHAR(100) NOT NULL,
            ID_Comercio CHAR(36) NOT NULL,
            Nombre_Comercio VARCHAR(100) NOT NULL,
            Direccion_Comercio TEXT NOT NULL,
            Telefono_Comercio VARCHAR(15) NOT NULL,
            Pago_Ensamblador DECIMAL(10,2) DEFAULT 400.00,
            Pago_Comprobador DECIMAL(10,2) DEFAULT 400.00,
            Pago_Mantenimiento DECIMAL(10,2) DEFAULT 400.00,
            empresa_nombre VARCHAR(100) DEFAULT 'recreasys.s.a',
            empresa_descripcion VARCHAR(255) DEFAULT 'Una empresa encargada en el ciclo de vida de las maquinas recreativas',
            FOREIGN KEY (ID_Recaudacion) REFERENCES recaudaciones(ID_Recaudacion),
            FOREIGN KEY (ID_Comercio) REFERENCES Comercio(ID_Comercio)
        )");
        
        // Tabla: informe_detalle
        $this->connection->query("CREATE TABLE informe_detalle (
            ID_Informe_Detalle CHAR(36) PRIMARY KEY DEFAULT (UUID()),
            ID_Informe CHAR(36) NOT NULL,
            ID_Componente CHAR(36) NOT NULL,
            FOREIGN KEY (ID_Informe) REFERENCES informes_recaudacion(ID_Informe) ON DELETE CASCADE,
            FOREIGN KEY (ID_Componente) REFERENCES componente(ID_Componente)
        )");
        
        // Tabla: informe_distribucion
        $this->connection->query("CREATE TABLE informe_distribucion (
            ID_Distribucion CHAR(36) PRIMARY KEY DEFAULT (UUID()),
            ID_Maquina CHAR(36) NOT NULL,
            ID_Usuario_Comprobador CHAR(36) NOT NULL,
            ID_Comercio CHAR(36) NOT NULL,
            fecha_alta DATETIME DEFAULT CURRENT_TIMESTAMP,
            fecha_baja DATETIME NULL,
            estado ENUM('Operativa','Retirada','No operativa') DEFAULT 'Operativa',
            FOREIGN KEY (ID_Maquina) REFERENCES MaquinaRecreativa(ID_Maquina),
            FOREIGN KEY (ID_Usuario_Comprobador) REFERENCES usuario(ID_Usuario),
            FOREIGN KEY (ID_Comercio) REFERENCES Comercio(ID_Comercio)
        )");
        
        // Tabla: montaje
        $this->connection->query("CREATE TABLE montaje (
            ID_Montaje CHAR(36) PRIMARY KEY DEFAULT (UUID()),
            fecha DATETIME NOT NULL,
            ID_Maquina CHAR(36) NOT NULL, 
            ID_Componente CHAR(36),
            ID_Tecnico CHAR(36),
            detalle TEXT NOT NULL,
            FOREIGN KEY (ID_Maquina) REFERENCES MaquinaRecreativa(ID_Maquina),
            FOREIGN KEY (ID_Componente) REFERENCES componente(ID_Componente),
            FOREIGN KEY (ID_Tecnico) REFERENCES Tecnico(ID_Tecnico)
        )");
        
        // Tabla: historial_maquinas
 $this->connection->query("CREATE TABLE historial_maquinas (
    ID_Historial CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    ID_Maquina CHAR(36) NULL,  -- Cambiado de NOT NULL a NULL
    ID_Usuario CHAR(36) NOT NULL,
    tipo_usuario VARCHAR(50) NOT NULL,
    accion VARCHAR(100) NOT NULL,
    descripcion TEXT,
    estado_anterior VARCHAR(50),
    estado_nuevo VARCHAR(50),
    etapa_anterior VARCHAR(50),
    etapa_nueva VARCHAR(50),
    fecha_hora DATETIME DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    detalles_adicionales TEXT,
    INDEX idx_historial_maquina (ID_Maquina),
    INDEX idx_historial_usuario (ID_Usuario),
    INDEX idx_historial_fecha (fecha_hora),
    FOREIGN KEY (ID_Maquina) REFERENCES MaquinaRecreativa(ID_Maquina) ON DELETE CASCADE,
    FOREIGN KEY (ID_Usuario) REFERENCES usuario(ID_Usuario) ON DELETE CASCADE
) ENGINE=InnoDB");
   # ALTER TABLE historial_maquinas MODIFY ID_Maquina CHAR(36) NULL;     
        $this->connection->query("SET FOREIGN_KEY_CHECKS = 1");
    }
    
    /**
     * Insertar datos iniciales (usuario admin por defecto)
     */
    private function insertInitialData(): void
    {
        // Insertar usuario administrador por defecto
        $adminId = $this->generateUUID();
        $hashedPassword = password_hash('admin123', PASSWORD_BCRYPT);
        $emailEncrypted = CifradoHelper::encriptar('admin@test.com');
        $ciEncrypted = CifradoHelper::encriptar('1234567890');
        
        $stmt = $this->connection->prepare("INSERT INTO usuario (ID_Usuario, nombre, apellido, ci, email, usuario_asignado, contrasena, tipo, estado, fecha_registro) 
            VALUES (?, 'Admin', 'Test', ?, ?, 'admin_test', ?, 'Administrador', 'Activo', NOW())");
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
    
    /**
     * Limpia completamente la base de datos de prueba
     */
    public function cleanDatabase(): void
    {
        $this->connection->query("SET FOREIGN_KEY_CHECKS = 0");
        
        $tables = [
            'comentario', 'notificaciones', 'reporte', 'componente_usuario',
            'montaje', 'componente', 'informe_detalle', 'informes_recaudacion',
            'recaudaciones', 'informe_distribucion', 'NotificacionMaquinaRecreativa',
            'historial_maquinas', 'MaquinaRecreativa', 'Comercio', 'inicio_sesion',
            'historial_actividades', 'Logistica', 'Tecnico', 'usuario'
        ];
        
        foreach ($tables as $table) {
            $this->connection->query("TRUNCATE TABLE $table");
        }
        
        $this->connection->query("SET FOREIGN_KEY_CHECKS = 1");
        
        // Volver a insertar datos iniciales
        $this->insertInitialData();
    }
}