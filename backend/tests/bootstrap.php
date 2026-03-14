<?php
// Configurar el ambiente para las pruebas:
error_reporting(E_ALL & ~E_DEPRECATED);
ini_set('display_errors', '1');

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

// Preparar la base de datos de prueba:
require_once __DIR__.'/TestDatabase.php';

// Inicializar la base de datos de prueba
$testDb = new TestDatabase();
$conn = $testDb->getConnection();

?>