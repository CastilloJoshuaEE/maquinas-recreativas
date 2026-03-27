<?php
/**
 * backend/public/index.php
 * maquinas_recreativas - Front Controller
 * 
 * Punto de entrada único de la aplicación. Su única responsabilidad es
 * cargar el bootstrap y ejecutar la aplicación.
 * 
 * @package maquinas_recreativas\Public
 * @author Tu Equipo
 * @version 1.0
 */
// Cargar bootstrap de la aplicación
require_once __DIR__ .'/../bootstrap/app.php';
// Inicializar y ejecutar la aplicación
use maquinas_recreativas\Core\App;
$app = App::getInstance();
$app->run();