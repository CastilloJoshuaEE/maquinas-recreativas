<?php
/**
 * Base de datos para pruebas unitarias
 * 
 * @package maquinas_recreativas\Tests
 */

namespace maquinas_recreativas\Tests;

use PDO;
use PDOException;
use maquinas_recreativas\Infrastructure\Database\Database;
use maquinas_recreativas\Infrastructure\Security\CifradoHelper;

class TestDatabase extends Database
{
    private static ?TestDatabase $instance = null;
    private string $testDbName;
    private ?PDO $testConnection = null;
    private bool $tablesCreated = false;

    private function __construct()
    {
        // Llamar al constructor padre
        parent::__construct();
        
        $this->testDbName = getenv('DB_NAME_TEST') ?: 'test_bd_recrea_sys';
        
        // Crear base de datos de prueba
        $this->createTestDatabase();
        
        // Reconectar a la base de datos de prueba
        $this->testConnection = $this->reconnectToTestDb();
        
        // Sobrescribir la conexión padre con la de prueba
        $this->overrideConnection($this->testConnection);
        
        // Crear tablas y datos iniciales
        $this->createTables();
        $this->setupDatabase();

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
 * Ejecuta la configuración completa (procedimientos y permisos)
 */
private function setupDatabase(): void
{
    try {
        $setup = new SetupTestDatabase($this->connection, $this->testDbName);
        $setup->run();
    } catch (\Exception $e) {
        echo "Error en setup: " . $e->getMessage() . "\n";
        // No lanzamos excepción para que las pruebas puedan continuar
        // usando los repositorios sin SPs si falla
    }
}

    private function createTestDatabase(): void
    {
        // Usar la conexión padre para crear la base de datos
        $conn = parent::getConnection();
        $driver = $this->getDriver();

        if ($driver === 'pgsql') {
            // PostgreSQL: verificar si la base de datos existe
            try {
                $stmt = $conn->prepare("SELECT 1 FROM pg_database WHERE datname = ?");
                $stmt->execute([$this->testDbName]);
                $exists = $stmt->fetchColumn();
                
                if (!$exists) {
                    $conn->exec("CREATE DATABASE {$this->testDbName}");
                }
            } catch (PDOException $e) {
                // Si la base de datos ya existe, ignorar el error
                if (strpos($e->getMessage(), 'already exists') === false) {
                    throw $e;
                }
            }
        } else {
            // MySQL
            $conn->exec("CREATE DATABASE IF NOT EXISTS {$this->testDbName}");
        }
    }

    private function reconnectToTestDb(): PDO
    {
        $host = getenv('DB_HOST') ?: 'localhost';
        $port = getenv('DB_PORT') ?: ($this->isPostgreSQL() ? '5432' : '3306');
        $user = getenv('DB_USER') ?: 'root';
        $pass = getenv('DB_PASS') ?: '';
        $driver = $this->getDriver();

        $dsn = match($driver) {
            'pgsql' => "pgsql:host={$host};port={$port};dbname={$this->testDbName}",
            'mysql' => "mysql:host={$host};port={$port};dbname={$this->testDbName};charset=utf8mb4",
            default => throw new \RuntimeException("Driver no soportado: {$driver}")
        };

        return new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }

    /**
     * Sobrescribe la conexión de la clase padre
     * 
     * @param PDO $connection Conexión a establecer
     */
    private function overrideConnection(PDO $connection): void
    {
        $reflection = new \ReflectionClass(parent::class);
        $property = $reflection->getProperty('connection');
        $property->setAccessible(true);
        $property->setValue($this, $connection);
    }

    private function createTables(): void
    {
        if ($this->tablesCreated) {
            return;
        }

        // Desactivar verificaciones de clave foránea
        if ($this->isPostgreSQL()) {
            $this->connection->exec("SET session_replication_role = 'replica'");
        } else {
            $this->connection->exec("SET FOREIGN_KEY_CHECKS = 0");
        }

        // Eliminar todas las tablas en orden inverso a las dependencias
        $tables = [
            'comentario', 'notificaciones', 'reporte', 'componente_usuario',
            'montaje', 'componente', 'informe_detalle', 'informes_recaudacion',
            'recaudaciones', 'informe_distribucion', 'NotificacionMaquinaRecreativa',
            'historial_maquinas', 'MaquinaRecreativa', 'Comercio', 'inicio_sesion',
            'historial_actividades', 'Logistica', 'Tecnico', 'usuario'
        ];

        foreach ($tables as $table) {
            try {
                if ($this->isPostgreSQL()) {
                    $this->connection->exec("DROP TABLE IF EXISTS \"$table\" CASCADE");
                } else {
                    $this->connection->exec("DROP TABLE IF EXISTS `$table`");
                }
            } catch (PDOException $e) {
                // Ignorar errores de tablas que no existen
            }
        }

        // Crear tablas según el driver
        if ($this->isPostgreSQL()) {
            $this->createTablesPostgreSQL();
        } else {
            $this->createTablesMySQL();
        }

        // Reactivar verificaciones de clave foránea
        if ($this->isPostgreSQL()) {
            $this->connection->exec("SET session_replication_role = 'origin'");
        } else {
            $this->connection->exec("SET FOREIGN_KEY_CHECKS = 1");
        }

        $this->tablesCreated = true;
    }

    private function createTablesMySQL(): void
    {
        // Tabla: usuario
        $this->connection->exec("CREATE TABLE usuario (
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
        $this->connection->exec("CREATE TABLE Tecnico (
            ID_Tecnico CHAR(36) PRIMARY KEY,
            Especialidad ENUM('Ensamblador', 'Comprobador', 'Mantenimiento') NOT NULL,
            Cantidad_Actividades INT DEFAULT 0 NOT NULL,
            FOREIGN KEY (ID_Tecnico) REFERENCES usuario(ID_Usuario) ON DELETE CASCADE
        )");

        // Tabla: Logistica
        $this->connection->exec("CREATE TABLE Logistica (
            ID_Logistica CHAR(36) PRIMARY KEY,
            FOREIGN KEY (ID_Logistica) REFERENCES usuario(ID_Usuario) ON DELETE CASCADE
        )");

        // Tabla: historial_actividades
        $this->connection->exec("CREATE TABLE historial_actividades (
            ID_Historial_Actividades CHAR(36) PRIMARY KEY DEFAULT (UUID()),
            ID_Usuario CHAR(36) NOT NULL,
            descripcion TEXT NOT NULL,
            fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (ID_Usuario) REFERENCES usuario(ID_Usuario) ON DELETE CASCADE
        )");

        // Tabla: inicio_sesion
        $this->connection->exec("CREATE TABLE inicio_sesion (
            ID_Inicio_Sesion CHAR(36) PRIMARY KEY DEFAULT (UUID()),
            ID_Usuario CHAR(36) NOT NULL,
            usuario_asignado VARCHAR(100) NOT NULL,
            contrasena VARCHAR(255) NOT NULL,
            fecha_inicio DATETIME DEFAULT CURRENT_TIMESTAMP,
            fecha_ultima_sesion DATETIME NULL,
            FOREIGN KEY (ID_Usuario) REFERENCES usuario(ID_Usuario) ON DELETE CASCADE
        )");

        // Tabla: Comercio
        $this->connection->exec("CREATE TABLE Comercio (
            ID_Comercio CHAR(36) PRIMARY KEY DEFAULT (UUID()),
            Nombre VARCHAR(100) NOT NULL UNIQUE,
            Tipo ENUM('Minorista', 'Mayorista') NOT NULL,
            Direccion TEXT NOT NULL,
            Telefono VARCHAR(15) NOT NULL UNIQUE,
            Cantidad_Maquinas INT DEFAULT 0 NOT NULL,
            Fecha_Registro DATE NOT NULL
        )");

        // Tabla: MaquinaRecreativa
        $this->connection->exec("CREATE TABLE MaquinaRecreativa (
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
        $this->connection->exec("CREATE TABLE componente (
            ID_Componente CHAR(36) PRIMARY KEY DEFAULT (UUID()),
            tipo ENUM('Logistico', 'Electronico', 'Estructural', 'Accesorio') NOT NULL,
            nombre VARCHAR(50) NOT NULL,
            precio DECIMAL(10,2) DEFAULT 10.00
        )");

        // Tabla: componente_usuario
        $this->connection->exec("CREATE TABLE componente_usuario (
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
        $this->connection->exec("CREATE TABLE reporte (
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
        $this->connection->exec("CREATE TABLE notificaciones (
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
        $this->connection->exec("CREATE TABLE comentario (
            ID_Comentario CHAR(36) PRIMARY KEY DEFAULT (UUID()),
            ID_Reporte CHAR(36) NOT NULL,
            ID_Usuario_Emisor CHAR(36) NOT NULL, 
            fecha_hora DATETIME NOT NULL,
            comentario TEXT NOT NULL,
            fecha_edicion DATETIME NULL,
            eliminado BOOLEAN DEFAULT FALSE,
            FOREIGN KEY (ID_Reporte) REFERENCES reporte(ID_Reporte) ON DELETE CASCADE,
            FOREIGN KEY (ID_Usuario_Emisor) REFERENCES usuario(ID_Usuario) ON DELETE CASCADE
        )");

        // Tabla: NotificacionMaquinaRecreativa
        $this->connection->exec("CREATE TABLE NotificacionMaquinaRecreativa (
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
        $this->connection->exec("CREATE TABLE recaudaciones (
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
        $this->connection->exec("CREATE TABLE informes_recaudacion (
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
        $this->connection->exec("CREATE TABLE informe_detalle (
            ID_Informe_Detalle CHAR(36) PRIMARY KEY DEFAULT (UUID()),
            ID_Informe CHAR(36) NOT NULL,
            ID_Componente CHAR(36) NOT NULL,
            FOREIGN KEY (ID_Informe) REFERENCES informes_recaudacion(ID_Informe) ON DELETE CASCADE,
            FOREIGN KEY (ID_Componente) REFERENCES componente(ID_Componente)
        )");

        // Tabla: informe_distribucion
        $this->connection->exec("CREATE TABLE informe_distribucion (
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
        $this->connection->exec("CREATE TABLE montaje (
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
        $this->connection->exec("CREATE TABLE historial_maquinas (
            ID_Historial CHAR(36) PRIMARY KEY DEFAULT (UUID()),
            ID_Maquina CHAR(36) NULL,
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
        )");
    }

    private function createTablesPostgreSQL(): void
    {
        // Tabla: usuario
        $this->connection->exec("CREATE TABLE usuario (
            ID_Usuario UUID PRIMARY KEY DEFAULT gen_random_uuid(),
            ci VARCHAR(100) NOT NULL UNIQUE,
            nombre VARCHAR(50) NOT NULL,
            apellido VARCHAR(50) NOT NULL,
            email VARCHAR(100) NOT NULL UNIQUE,
            tipo VARCHAR(20) NOT NULL CHECK (tipo IN ('Administrador', 'Logistica', 'Tecnico', 'Contabilidad', 'Usuario')),
            usuario_asignado VARCHAR(25) NOT NULL UNIQUE DEFAULT 'Aun no tiene',
            contrasena VARCHAR(255) NOT NULL DEFAULT 'Aun no tiene',
            estado VARCHAR(25) NOT NULL DEFAULT 'Pendiente de asignacion' CHECK (estado IN ('Pendiente de asignacion', 'Activo', 'Inhabilitado')),
            fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        // Tabla: Tecnico
        $this->connection->exec("CREATE TABLE Tecnico (
            ID_Tecnico UUID PRIMARY KEY,
            Especialidad VARCHAR(20) NOT NULL CHECK (Especialidad IN ('Ensamblador', 'Comprobador', 'Mantenimiento')),
            Cantidad_Actividades INT DEFAULT 0 NOT NULL,
            FOREIGN KEY (ID_Tecnico) REFERENCES usuario(ID_Usuario) ON DELETE CASCADE
        )");

        // Tabla: Logistica
        $this->connection->exec("CREATE TABLE Logistica (
            ID_Logistica UUID PRIMARY KEY,
            FOREIGN KEY (ID_Logistica) REFERENCES usuario(ID_Usuario) ON DELETE CASCADE
        )");

        // Tabla: historial_actividades
        $this->connection->exec("CREATE TABLE historial_actividades (
            ID_Historial_Actividades UUID PRIMARY KEY DEFAULT gen_random_uuid(),
            ID_Usuario UUID NOT NULL,
            descripcion TEXT NOT NULL,
            fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (ID_Usuario) REFERENCES usuario(ID_Usuario) ON DELETE CASCADE
        )");

        // Tabla: inicio_sesion
        $this->connection->exec("CREATE TABLE inicio_sesion (
            ID_Inicio_Sesion UUID PRIMARY KEY DEFAULT gen_random_uuid(),
            ID_Usuario UUID NOT NULL,
            usuario_asignado VARCHAR(100) NOT NULL,
            contrasena VARCHAR(255) NOT NULL,
            fecha_inicio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            fecha_ultima_sesion TIMESTAMP NULL,
            FOREIGN KEY (ID_Usuario) REFERENCES usuario(ID_Usuario) ON DELETE CASCADE
        )");

        // Tabla: Comercio
        $this->connection->exec("CREATE TABLE Comercio (
            ID_Comercio UUID PRIMARY KEY DEFAULT gen_random_uuid(),
            Nombre VARCHAR(100) NOT NULL UNIQUE,
            Tipo VARCHAR(20) NOT NULL CHECK (Tipo IN ('Minorista', 'Mayorista')),
            Direccion TEXT NOT NULL,
            Telefono VARCHAR(15) NOT NULL UNIQUE,
            Cantidad_Maquinas INT DEFAULT 0 NOT NULL,
            Fecha_Registro DATE NOT NULL
        )");

        // Tabla: MaquinaRecreativa
        $this->connection->exec("CREATE TABLE MaquinaRecreativa (
            ID_Maquina UUID PRIMARY KEY DEFAULT gen_random_uuid(),
            Nombre_Maquina VARCHAR(100) NOT NULL,
            Tipo VARCHAR(50) NOT NULL,
            Etapa VARCHAR(20) NOT NULL DEFAULT 'Montaje' CHECK (Etapa IN ('Montaje', 'Distribucion', 'Recaudacion')),
            Estado VARCHAR(25) NOT NULL DEFAULT 'Ensamblandose' CHECK (Estado IN ('Ensamblandose', 'Comprobandose', 'Reensamblandose', 'Distribuyendose', 'Operativa', 'No operativa', 'Retirada')),
            Fecha_Registro DATE NOT NULL,
            ID_Tecnico_Ensamblador UUID NOT NULL,
            ID_Tecnico_Comprobador UUID NOT NULL,
            ID_Comercio UUID NOT NULL,
            ID_Tecnico_Mantenimiento UUID,
            FOREIGN KEY (ID_Tecnico_Ensamblador) REFERENCES Tecnico(ID_Tecnico),
            FOREIGN KEY (ID_Tecnico_Comprobador) REFERENCES Tecnico(ID_Tecnico),
            FOREIGN KEY (ID_Comercio) REFERENCES Comercio(ID_Comercio),
            FOREIGN KEY (ID_Tecnico_Mantenimiento) REFERENCES Tecnico(ID_Tecnico)
        )");

        // Tabla: componente
        $this->connection->exec("CREATE TABLE componente (
            ID_Componente UUID PRIMARY KEY DEFAULT gen_random_uuid(),
            tipo VARCHAR(20) NOT NULL CHECK (tipo IN ('Logistico', 'Electronico', 'Estructural', 'Accesorio')),
            nombre VARCHAR(50) NOT NULL,
            precio DECIMAL(10,2) DEFAULT 10.00
        )");

        // Tabla: componente_usuario
        $this->connection->exec("CREATE TABLE componente_usuario (
            ID_Registro UUID PRIMARY KEY DEFAULT gen_random_uuid(),
            ID_Componente UUID NOT NULL,
            ID_Usuario UUID NOT NULL,
            fecha_asignacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            fecha_liberacion TIMESTAMP NULL,
            ID_Maquina UUID NULL,
            FOREIGN KEY (ID_Componente) REFERENCES componente(ID_Componente),
            FOREIGN KEY (ID_Usuario) REFERENCES usuario(ID_Usuario) ON DELETE CASCADE,
            FOREIGN KEY (ID_Maquina) REFERENCES MaquinaRecreativa(ID_Maquina)
        )");

        // Tabla: reporte
        $this->connection->exec("CREATE TABLE reporte (
            ID_Reporte UUID PRIMARY KEY DEFAULT gen_random_uuid(),
            ID_Usuario_Emisor UUID NOT NULL,
            ID_Usuario_Destinatario UUID,
            fecha_hora TIMESTAMP NOT NULL,
            descripcion TEXT NOT NULL,
            estado VARCHAR(15) NOT NULL,
            FOREIGN KEY (ID_Usuario_Emisor) REFERENCES usuario(ID_Usuario) ON DELETE CASCADE,
            FOREIGN KEY (ID_Usuario_Destinatario) REFERENCES usuario(ID_Usuario) ON DELETE SET NULL
        )");

        // Tabla: notificaciones
        $this->connection->exec("CREATE TABLE notificaciones (
            ID_Notificaciones UUID PRIMARY KEY DEFAULT gen_random_uuid(),
            ID_Reporte UUID NOT NULL,
            ID_Usuario UUID NOT NULL,
            fecha_hora TIMESTAMP NOT NULL,
            mensaje TEXT NOT NULL,
            leida BOOLEAN DEFAULT FALSE,
            FOREIGN KEY (ID_Reporte) REFERENCES reporte(ID_Reporte) ON DELETE CASCADE,
            FOREIGN KEY (ID_Usuario) REFERENCES usuario(ID_Usuario) ON DELETE CASCADE
        )");

        // Tabla: comentario
        $this->connection->exec("CREATE TABLE comentario (
            ID_Comentario UUID PRIMARY KEY DEFAULT gen_random_uuid(),
            ID_Reporte UUID NOT NULL,
            ID_Usuario_Emisor UUID NOT NULL, 
            fecha_hora TIMESTAMP NOT NULL,
            comentario TEXT NOT NULL,
            fecha_edicion TIMESTAMP NULL,
            eliminado BOOLEAN DEFAULT FALSE,
            FOREIGN KEY (ID_Reporte) REFERENCES reporte(ID_Reporte) ON DELETE CASCADE,
            FOREIGN KEY (ID_Usuario_Emisor) REFERENCES usuario(ID_Usuario) ON DELETE CASCADE
        )");

        // Tabla: NotificacionMaquinaRecreativa
        $this->connection->exec("CREATE TABLE NotificacionMaquinaRecreativa (
            ID_Notificacion UUID PRIMARY KEY DEFAULT gen_random_uuid(),
            ID_Remitente UUID NOT NULL,
            ID_Destinatario UUID NOT NULL,
            ID_Maquina UUID NOT NULL,
            Tipo VARCHAR(40) NOT NULL CHECK (Tipo IN (
                'Nuevo montaje',
                'Comprobar maquina recreativa',
                'Reensamblar maquina recreativa',
                'Distribuir maquina recreativa',
                'Dar mantenimiento a maquina recreativa',
                'Maquina recreativa retirada',
                'Maquina recreativa reparada'
            )),
            Mensaje TEXT,
            Fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            Estado VARCHAR(10) NOT NULL DEFAULT 'No leido' CHECK (Estado IN ('Leido', 'No leido')),
            FOREIGN KEY (ID_Remitente) REFERENCES usuario(ID_Usuario) ON DELETE CASCADE,
            FOREIGN KEY (ID_Destinatario) REFERENCES usuario(ID_Usuario) ON DELETE CASCADE,
            FOREIGN KEY (ID_Maquina) REFERENCES MaquinaRecreativa(ID_Maquina) ON DELETE CASCADE
        )");

        // Tabla: recaudaciones
        $this->connection->exec("CREATE TABLE recaudaciones (
            ID_Recaudacion UUID PRIMARY KEY DEFAULT gen_random_uuid(),
            Tipo_Comercio VARCHAR(20) NOT NULL CHECK (Tipo_Comercio IN ('Minorista', 'Mayorista')), 
            ID_Maquina UUID NOT NULL,
            ID_Usuario UUID NOT NULL,
            Monto_Total DECIMAL(10,2) NOT NULL,
            Monto_Empresa DECIMAL(10,2),
            Monto_Comercio DECIMAL(10,2),
            Porcentaje_Comercio DECIMAL(5,2) DEFAULT 0,
            fecha TIMESTAMP NOT NULL,
            detalle TEXT NOT NULL,
            FOREIGN KEY (ID_Usuario) REFERENCES usuario(ID_Usuario),
            FOREIGN KEY (ID_Maquina) REFERENCES MaquinaRecreativa(ID_Maquina)
        )");

        // Tabla: informes_recaudacion
        $this->connection->exec("CREATE TABLE informes_recaudacion (
            ID_Informe UUID PRIMARY KEY DEFAULT gen_random_uuid(),
            ID_Recaudacion UUID NOT NULL,
            CI_Usuario VARCHAR(100) NOT NULL,
            Nombre_Maquina VARCHAR(100) NOT NULL,
            ID_Comercio UUID NOT NULL,
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
        $this->connection->exec("CREATE TABLE informe_detalle (
            ID_Informe_Detalle UUID PRIMARY KEY DEFAULT gen_random_uuid(),
            ID_Informe UUID NOT NULL,
            ID_Componente UUID NOT NULL,
            FOREIGN KEY (ID_Informe) REFERENCES informes_recaudacion(ID_Informe) ON DELETE CASCADE,
            FOREIGN KEY (ID_Componente) REFERENCES componente(ID_Componente)
        )");

        // Tabla: informe_distribucion
        $this->connection->exec("CREATE TABLE informe_distribucion (
            ID_Distribucion UUID PRIMARY KEY DEFAULT gen_random_uuid(),
            ID_Maquina UUID NOT NULL,
            ID_Usuario_Comprobador UUID NOT NULL,
            ID_Comercio UUID NOT NULL,
            fecha_alta TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            fecha_baja TIMESTAMP NULL,
            estado VARCHAR(15) DEFAULT 'Operativa' CHECK (estado IN ('Operativa','Retirada','No operativa')),
            FOREIGN KEY (ID_Maquina) REFERENCES MaquinaRecreativa(ID_Maquina),
            FOREIGN KEY (ID_Usuario_Comprobador) REFERENCES usuario(ID_Usuario),
            FOREIGN KEY (ID_Comercio) REFERENCES Comercio(ID_Comercio)
        )");

        // Tabla: montaje
        $this->connection->exec("CREATE TABLE montaje (
            ID_Montaje UUID PRIMARY KEY DEFAULT gen_random_uuid(),
            fecha TIMESTAMP NOT NULL,
            ID_Maquina UUID NOT NULL, 
            ID_Componente UUID,
            ID_Tecnico UUID,
            detalle TEXT NOT NULL,
            FOREIGN KEY (ID_Maquina) REFERENCES MaquinaRecreativa(ID_Maquina),
            FOREIGN KEY (ID_Componente) REFERENCES componente(ID_Componente),
            FOREIGN KEY (ID_Tecnico) REFERENCES Tecnico(ID_Tecnico)
        )");

        // Tabla: historial_maquinas
        $this->connection->exec("CREATE TABLE historial_maquinas (
            ID_Historial UUID PRIMARY KEY DEFAULT gen_random_uuid(),
            ID_Maquina UUID,
            ID_Usuario UUID NOT NULL,
            tipo_usuario VARCHAR(20) NOT NULL CHECK (tipo_usuario IN ('Tecnico', 'Logistica', 'Administrador')),
            accion VARCHAR(100) NOT NULL,
            descripcion TEXT,
            estado_anterior VARCHAR(50),
            estado_nuevo VARCHAR(50),
            etapa_anterior VARCHAR(50),
            etapa_nueva VARCHAR(50),
            fecha_hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            ip_address VARCHAR(45),
            detalles_adicionales JSONB,
            CONSTRAINT fk_historial_maquina 
                FOREIGN KEY (ID_Maquina) 
                REFERENCES MaquinaRecreativa(ID_Maquina) 
                ON DELETE CASCADE,
            CONSTRAINT fk_historial_usuario 
                FOREIGN KEY (ID_Usuario) 
                REFERENCES usuario(ID_Usuario) 
                ON DELETE CASCADE
        )");
    }
private function insertInitialData(): void
{
    // Primero, asegurar que existe el usuario administrador
    $adminId = $this->generateUUID();
    $hashedPassword = password_hash('admin123', PASSWORD_BCRYPT);
    $emailEncrypted = CifradoHelper::encriptar('admin@test.com');
    $ciEncrypted = CifradoHelper::encriptar('1234567890');

    // Verificar si admin_test ya existe
    $checkStmt = $this->connection->prepare("SELECT COUNT(*) FROM usuario WHERE usuario_asignado = 'admin_test'");
    $checkStmt->execute();
    $adminExists = $checkStmt->fetchColumn();

    if (!$adminExists) {
        $sql = "INSERT INTO usuario (ID_Usuario, nombre, apellido, ci, email, usuario_asignado, contrasena, tipo, estado, fecha_registro) 
                VALUES (?, 'Admin', 'Test', ?, ?, 'admin_test', ?, 'Administrador', 'Activo', NOW())";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([$adminId, $ciEncrypted, $emailEncrypted, $hashedPassword]);
    }

    
}

    public function generateUUID(): string
    {
        if ($this->isPostgreSQL()) {
            $stmt = $this->connection->query("SELECT gen_random_uuid() as uuid");
        } else {
            $stmt = $this->connection->query("SELECT UUID() as uuid");
        }
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['uuid'];
    }

    public function getTestConnection(): PDO
    {
        return $this->testConnection ?? $this->connection;
    }

    public function close(): void
    {
        $this->connection = null;
        $this->testConnection = null;
    }

// TestDatabase.php - Método beginTransaction()
public function beginTransaction(): bool
{
    try {
        // Verificar que la conexión existe
        if (!$this->connection) {
            error_log("ERROR: Connection is null in beginTransaction");
            return false;
        }
        
        // Verificar si ya hay una transacción activa
        if ($this->connection->inTransaction()) {
            error_log("WARNING: Transaction already active");
            return true;
        }
        
        return $this->connection->beginTransaction();
    } catch (PDOException $e) {
        error_log("Error starting transaction: " . $e->getMessage());
        return false;
    }
}

    public function commit(): bool
    {
        return $this->connection->commit();
    }

    public function rollback(): bool
    {
        return $this->connection->rollBack();
    }

    public function cleanDatabase(): void
{
    // Desactivar verificaciones de clave foránea
    if ($this->isPostgreSQL()) {
        $this->connection->exec("SET session_replication_role = 'replica'");
    } else {
        $this->connection->exec("SET FOREIGN_KEY_CHECKS = 0");
    }

    $tables = [
        'comentario', 'notificaciones', 'reporte', 'componente_usuario',
        'montaje', 'componente', 'informe_detalle', 'informes_recaudacion',
        'recaudaciones', 'informe_distribucion', 'NotificacionMaquinaRecreativa',
        'historial_maquinas', 'MaquinaRecreativa', 'Comercio', 'inicio_sesion',
        'historial_actividades', 'Logistica', 'Tecnico', 'usuario'
    ];

    // Eliminar en orden inverso a las dependencias
    foreach (array_reverse($tables) as $table) {
        try {
            if ($this->isPostgreSQL()) {
                $this->connection->exec("TRUNCATE TABLE \"$table\" RESTART IDENTITY CASCADE");
            } else {
                $this->connection->exec("TRUNCATE TABLE `$table`");
            }
        } catch (PDOException $e) {
            try {
                if ($this->isPostgreSQL()) {
                    $this->connection->exec("DELETE FROM \"$table\"");
                } else {
                    $this->connection->exec("DELETE FROM `$table`");
                }
            } catch (PDOException $e2) {
                // Ignorar
            }
        }
    }

    // Reactivar verificaciones de clave foránea
    if ($this->isPostgreSQL()) {
        $this->connection->exec("SET session_replication_role = 'origin'");
    } else {
        $this->connection->exec("SET FOREIGN_KEY_CHECKS = 1");
    }

    // Volver a insertar datos iniciales - asegurar que se inserten correctamente
    $this->insertInitialData();
}
}