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
