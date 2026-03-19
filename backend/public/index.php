<?php
/**
 * RecreaSys - Front Controller
 * 
 * Punto de entrada único de la aplicación. Su única responsabilidad es
 * cargar el bootstrap y ejecutar la aplicación.
 * 
 * @package RecreaSys\Public
 * @author Tu Equipo
 * @version 1.0
 */
// Cargar bootstrap de la aplicación
require_once __DIR__ .'/../bootstrap/app.php';
// Inicializar y ejecutar la aplicación
use RecreaSys\Core\App;
$app = App::getInstance();
$app->run();