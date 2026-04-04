<?php
/**
 * backend/bootstrap/env.php
 * maquinas_recreativas - Environment Loader
 * 
 * Carga las variables de entorno desde el archivo .env
 * 
 * @package maquinas_recreativas\Bootstrap
 * @author Tu Equipo
 * @version 1.0
 */
require_once __DIR__ . '/../Config/env.php';

// Asegurar que se carguen las variables
EnvManager::load();

// Definir constantes globales (opcional pero útil)
if (!defined('APP_ENV')) {
    define('APP_ENV', EnvManager::get('APP_ENV', 'production'));
}

if (!defined('APP_DEBUG')) {
    define('APP_DEBUG', EnvManager::get('APP_DEBUG', false));
}

if (!defined('DB_HOST')) {
    define('DB_HOST', EnvManager::get('DB_HOST', 'mysql'));
}

if (!defined('DB_PORT')) {
    define('DB_PORT', EnvManager::get('DB_PORT', 3306));
}

if (!defined('DB_NAME')) {
    define('DB_NAME', EnvManager::getDatabaseName());
}

if (!defined('DB_USER')) {
    define('DB_USER', EnvManager::get('DB_USER', 'root'));
}

if (!defined('DB_PASS')) {
    define('DB_PASS', EnvManager::get('DB_PASS', ''));
}