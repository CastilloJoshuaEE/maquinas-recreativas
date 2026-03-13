<?php
    // Configurar el ambiente para las pruebas:
    error_reporting(E_ALL & ~E_DEPRECATED); // Oculta advertencias obsoletas.
    ini_set('display_errors', '1'); // Muestra errores.

    // Cargar las herramientas necesarias:
    require_once __DIR__.'/../models/UsuarioModel.php';
    require_once __DIR__.'/../models/AdministradorModel.php'; 
    require_once __DIR__ . '/exceptions/ValidacionDatosException.php';
    require_once __DIR__.'/../models/MaquinaModel.php';
    require_once __DIR__.'/../models/ComercioModel.php';
    require_once __DIR__.'/../models/ComentarioModel.php';
    require_once __DIR__.'/../models/NotificacionModel.php';
    require_once __DIR__.'/../models/ReporteModel.php';
    require_once __DIR__.'/../models/ComponenteModel.php';
    require_once __DIR__.'/../models/InformeModel.php';
    require_once __DIR__.'/../models/DistribucionModel.php';
require_once __DIR__.'/exceptions/ValidacionDatosException.php';
    // Preparar la base de datos de prueba:
    require_once __DIR__.'/TestDatabase.php';


    $testDb = new TestDatabase();
    $conn = $testDb->getConnection();

    // Crear tablas vacías para las pruebas:
    // Tabla: usuario
    $conn->query("CREATE TABLE IF NOT EXISTS usuario (
        ID_Usuario INT AUTO_INCREMENT PRIMARY KEY,
        ci CHAR(10) NOT NULL UNIQUE,
        nombre VARCHAR(50) NOT NULL,
        apellido VARCHAR(50) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        tipo ENUM('Administrador', 'Logistica', 'Tecnico', 'Contabilidad') NOT NULL,
        usuario_asignado VARCHAR(25) NOT NULL DEFAULT 'Aun no tiene',
        contrasena VARCHAR(255) NOT NULL DEFAULT 'Aun no tiene',
        estado ENUM('Pendiente de asignacion', 'Activo', 'Inhabilitado') DEFAULT 'Pendiente de asignacion' NOT NULL
    )");

    // Tabla: inicio_sesion
    $conn->query("CREATE TABLE IF NOT EXISTS inicio_sesion (
        ID_Inicio_Sesion INT AUTO_INCREMENT PRIMARY KEY,
        ID_Usuario INT NOT NULL,
        usuario_asignado VARCHAR(100) NOT NULL,
        contrasena VARCHAR(255) NOT NULL,
        fecha_inicio DATETIME DEFAULT CURRENT_TIMESTAMP,
        fecha_ultima_sesion DATETIME NULL,
        FOREIGN KEY (ID_Usuario) REFERENCES usuario(ID_Usuario)
    )");

    // Tabla: Tecnico
    $conn->query("CREATE TABLE IF NOT EXISTS Tecnico (
        ID_Tecnico INT PRIMARY KEY,
        Especialidad ENUM('Ensamblador', 'Comprobador', 'Mantenimiento') NOT NULL,
        Cantidad_Actividades INT DEFAULT 0 NOT NULL,
        FOREIGN KEY (ID_Tecnico) REFERENCES usuario(ID_Usuario)
    )");

    // Tabla: Logistica
    $conn->query("CREATE TABLE IF NOT EXISTS Logistica (
        ID_Logistica INT PRIMARY KEY,
        FOREIGN KEY (ID_Logistica) REFERENCES usuario(ID_Usuario)
    )");

    // Tabla: historial_actividades
    $conn->query("CREATE TABLE IF NOT EXISTS historial_actividades (
        ID_Historial_Actividades INT AUTO_INCREMENT PRIMARY KEY,
        ID_Usuario INT NOT NULL,
        descripcion TEXT DEFAULT 'Estuvo en su main',
        fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (ID_Usuario) REFERENCES usuario(ID_Usuario)
    )");

    // Tabla: Comercio
    $conn->query("CREATE TABLE IF NOT EXISTS Comercio (
        ID_Comercio INT AUTO_INCREMENT PRIMARY KEY,
        Nombre VARCHAR(100) NOT NULL UNIQUE,
        Tipo ENUM('Minorista', 'Mayorista') NOT NULL,
        Direccion TEXT NOT NULL,
        Telefono VARCHAR(15) NOT NULL UNIQUE,
        Cantidad_Maquinas INT DEFAULT 0 NOT NULL,
        Fecha_Registro DATE NOT NULL
    )");

    // Tabla: MaquinaRecreativa
    $conn->query("CREATE TABLE IF NOT EXISTS MaquinaRecreativa (
        ID_Maquina INT AUTO_INCREMENT PRIMARY KEY,
        Nombre_Maquina VARCHAR(100) NOT NULL,
        Tipo VARCHAR(50) NOT NULL,
        Etapa ENUM('Montaje', 'Distribucion', 'Recaudacion') DEFAULT 'Montaje' NOT NULL,
        Estado ENUM('Ensamblandose', 'Comprobandose', 'Reensamblandose', 'Distribuyendose', 'Operativa', 'No operativa', 'Retirada') DEFAULT 'Ensamblandose' NOT NULL,
        Fecha_Registro DATE NOT NULL,
        ID_Tecnico_Ensamblador INT NOT NULL,
        ID_Tecnico_Comprobador INT NOT NULL,
        ID_Comercio INT NOT NULL,
        ID_Tecnico_Mantenimiento INT,
        FOREIGN KEY (ID_Tecnico_Ensamblador) REFERENCES Tecnico(ID_Tecnico),
        FOREIGN KEY (ID_Tecnico_Comprobador) REFERENCES Tecnico(ID_Tecnico),
        FOREIGN KEY (ID_Comercio) REFERENCES Comercio(ID_Comercio),
        FOREIGN KEY (ID_Tecnico_Mantenimiento) REFERENCES Tecnico(ID_Tecnico)
    )");

    // Tabla: NotificacionMaquinaRecreativa
    $conn->query("CREATE TABLE IF NOT EXISTS NotificacionMaquinaRecreativa (
        ID_Notificacion INT AUTO_INCREMENT PRIMARY KEY,
        ID_Remitente INT NOT NULL,
        ID_Destinatario INT NOT NULL,
        ID_Maquina INT NOT NULL,
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
        FOREIGN KEY (ID_Remitente) REFERENCES usuario(ID_Usuario),
        FOREIGN KEY (ID_Destinatario) REFERENCES usuario(ID_Usuario),
        FOREIGN KEY (ID_Maquina) REFERENCES MaquinaRecreativa(ID_Maquina)
    )");

    $conn->query("CREATE INDEX IF NOT EXISTS idx_notificacion_maquina_estado ON NotificacionMaquinaRecreativa(Estado)");

    // Tabla: componente
    $conn->query("CREATE TABLE IF NOT EXISTS componente (
        ID_Componente INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        tipo ENUM('Ensamblador', 'Comprobador', 'Mantenimiento') NOT NULL,
        nombre VARCHAR(50) NOT NULL,
        precio DECIMAL(10,2) DEFAULT 10.00,
        cantidad_disponible INT DEFAULT 40
    )");

    // Tabla: informe
    $conn->query("CREATE TABLE IF NOT EXISTS informe (
        ID_Informe INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        CI_Usuario CHAR(10) NOT NULL,
        ID_Maquina INT NOT NULL,
        fecha_hora_inicio DATETIME NOT NULL,
        fecha_hora_fin DATETIME NOT NULL,
        descripcion VARCHAR(500) NOT NULL,
        observaciones TEXT NOT NULL,
        tipo VARCHAR(15) NOT NULL,
        FOREIGN KEY (ID_Maquina) REFERENCES MaquinaRecreativa(ID_Maquina)
    )");

    // Tabla: informe_detalle
    $conn->query("CREATE TABLE IF NOT EXISTS informe_detalle (
        ID_Informe_Detalle INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        ID_Informe INT NOT NULL,
        ID_Componente_Repuesto INT NOT NULL,
        porcentaje_empresa FLOAT NOT NULL,
        porcentaje_comercio FLOAT NOT NULL,
        monto_total_recaudado DECIMAL(10, 2) NOT NULL,
        mensualidad_comercio DECIMAL(10, 2) NOT NULL,
        FOREIGN KEY (ID_Informe) REFERENCES informe(ID_Informe),
        FOREIGN KEY (ID_Componente_Repuesto) REFERENCES componente(ID_Componente)
    )");

    // Tabla: reporte
    $conn->query("CREATE TABLE IF NOT EXISTS reporte (
        ID_Reporte INT AUTO_INCREMENT PRIMARY KEY,
        ID_Usuario_Emisor INT NOT NULL,
        ID_Usuario_Destinatario INT,
        fecha_hora DATETIME NOT NULL,
        descripcion TEXT NOT NULL,
        estado VARCHAR(15) NOT NULL,
        FOREIGN KEY (ID_Usuario_Emisor) REFERENCES usuario(ID_Usuario),
        FOREIGN KEY (ID_Usuario_Destinatario) REFERENCES usuario(ID_Usuario)
    )");

    // Tabla: notificaciones
    $conn->query("CREATE TABLE IF NOT EXISTS notificaciones (
        ID_Notificaciones INT AUTO_INCREMENT PRIMARY KEY,
        ID_Reporte INT NOT NULL,
        ID_Usuario INT NOT NULL,
        fecha_hora DATETIME NOT NULL,
        mensaje TEXT NOT NULL,
        leida BOOLEAN DEFAULT FALSE,
        FOREIGN KEY (ID_Reporte) REFERENCES reporte(ID_Reporte),
        FOREIGN KEY (ID_Usuario) REFERENCES usuario(ID_Usuario)
    )");

    // Tabla: comentario
    $conn->query("CREATE TABLE IF NOT EXISTS comentario (
        ID_Comentario INT AUTO_INCREMENT PRIMARY KEY,
        ID_Reporte INT NOT NULL,
        ID_Usuario_Emisor INT NOT NULL, 
        fecha_hora DATETIME NOT NULL,
        comentario TEXT NOT NULL,
        FOREIGN KEY (ID_Reporte) REFERENCES reporte(ID_Reporte),
        FOREIGN KEY (ID_Usuario_Emisor) REFERENCES usuario(ID_Usuario)
    )");

    // Tabla: recaudaciones
    $conn->query("CREATE TABLE IF NOT EXISTS recaudaciones (
        ID_Recaudacion INT AUTO_INCREMENT PRIMARY KEY,
        ID_Usuario INT NOT NULL,
        fecha DATETIME NOT NULL,
        detalle TEXT NOT NULL,
        FOREIGN KEY (ID_Usuario) REFERENCES usuario(ID_Usuario)
    )");

    // Tabla: distribucion
    $conn->query("CREATE TABLE IF NOT EXISTS distribucion (
        id_informe INT AUTO_INCREMENT PRIMARY KEY,
        id_maquina INT NOT NULL,
        id_usuario_logistica INT NOT NULL,
        id_comercio INT NOT NULL,
        fecha_inicio DATETIME NOT NULL,
        fecha_fin DATETIME NOT NULL,
        estado VARCHAR(50) NOT NULL,
        FOREIGN KEY (id_maquina) REFERENCES MaquinaRecreativa(ID_Maquina),
        FOREIGN KEY (id_usuario_logistica) REFERENCES usuario(ID_Usuario),
        FOREIGN KEY (id_comercio) REFERENCES Comercio(ID_Comercio)
    )");

    // Tabla: montajes
    $conn->query("CREATE TABLE IF NOT EXISTS montajes (
        ID_Montaje INT AUTO_INCREMENT PRIMARY KEY,
        ID_Usuario INT NOT NULL,
        fecha DATETIME NOT NULL,
        detalle TEXT NOT NULL,
        FOREIGN KEY (ID_Usuario) REFERENCES usuario(ID_Usuario)
    )");

    // Tabla: reparaciones
    $conn->query("CREATE TABLE IF NOT EXISTS reparaciones (
        ID_Reparaciones INT AUTO_INCREMENT PRIMARY KEY,
        ID_Maquina INT NOT NULL,
        fecha DATE NOT NULL,
        descripcion TEXT NOT NULL,
        tecnico VARCHAR(100),
        FOREIGN KEY (ID_Maquina) REFERENCES MaquinaRecreativa(ID_Maquina)
    )");

?>