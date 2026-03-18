<?php
// tests/bootstrap.php
// Configurar el ambiente para las pruebas:

error_reporting(E_ALL & ~E_DEPRECATED);
ini_set('display_errors', '1');

// Cargar clases necesarias
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helper/CifradoHelper.php';
require_once __DIR__ . '/../helper/RateLimiter.php';

// Cargar modelos
require_once __DIR__ . '/../models/UsuarioModel.php';
require_once __DIR__ . '/../models/AdministradorModel.php'; 
require_once __DIR__ . '/../models/MaquinaModel.php';
require_once __DIR__ . '/../models/ComercioModel.php';
require_once __DIR__ . '/../models/ComentarioModel.php';
require_once __DIR__ . '/../models/NotificacionModel.php';
require_once __DIR__ . '/../models/ReporteModel.php';
require_once __DIR__ . '/../models/ComponenteModel.php';
require_once __DIR__ . '/../models/InformeModel.php';
require_once __DIR__ . '/../models/DistribucionModel.php';

// Cargar servicios
require_once __DIR__ . '/../services/UsuarioService.php';
require_once __DIR__ . '/../services/AdministradorService.php';
require_once __DIR__ . '/../services/MaquinaService.php';
require_once __DIR__ . '/../services/ComercioService.php';
require_once __DIR__ . '/../services/ComentarioService.php';
require_once __DIR__ . '/../services/NotificacionService.php';
require_once __DIR__ . '/../services/ReporteService.php';
require_once __DIR__ . '/../services/ComponenteService.php';
require_once __DIR__ . '/../services/InformeService.php';

// Cargar excepciones
require_once __DIR__ . '/exceptions/ValidacionDatosException.php';

// Preparar la base de datos de prueba
require_once __DIR__ . '/TestDatabase.php';

// Inicializar la base de datos de prueba
$testDb = new TestDatabase();
$conn = $testDb->getConnection();

?>