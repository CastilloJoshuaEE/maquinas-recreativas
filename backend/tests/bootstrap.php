<?php
    // Configurar el ambiente para las pruebas:
    error_reporting(E_ALL & ~E_DEPRECATED); // Oculta advertencias obsoletas.
    ini_set('display_errors', '1'); // Muestra errores.

    // Cargar las herramientas necesarias:
    require_once __DIR__.'/TestDatabase.php'; // La base de datos falsa para pruebas.
    require_once __DIR__.'/../models/UsuarioModel.php'; // El modelo a probar.

    // Preparar la base de datos de prueba:
    $testDb = new TestDatabase();
    $conn = $testDb->getConnection();

    // Crear tablas vacías para las pruebas:
    $conn->query("CREATE TABLE IF NOT EXISTS usuario(
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

    $conn->query("CREATE TABLE IF NOT EXISTS Logistica(
        ID_Logistica INT PRIMARY KEY,
        FOREIGN KEY (ID_Logistica) REFERENCES usuario(ID_Usuario)
    )");

    $conn->query("CREATE TABLE IF NOT EXISTS Tecnico(
        ID_Tecnico INT PRIMARY KEY,
        Especialidad ENUM('Ensamblador', 'Comprobador', 'Mantenimiento') NOT NULL,
        Cantidad_Actividades INT DEFAULT 0 NOT NULL,
        FOREIGN KEY (ID_Tecnico) REFERENCES usuario(ID_Usuario)
    )");
?>