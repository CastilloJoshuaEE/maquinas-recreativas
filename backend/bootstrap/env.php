<?php
/**
 * maquinas_recreativas - Environment Loader
 * 
 * Carga las variables de entorno desde el archivo .env
 * 
 * @package maquinas_recreativas\Bootstrap
 * @author Tu Equipo
 * @version 1.0
 */
$envPath = __DIR__ .'/../.env';
if(!file_exists($envPath)) {
    die("Error: Archivo .env no encontrado en:".$envPath);
}
$env= parse_ini_file($envPath);
if($env===false) {
    die("Error: No se pudo parsear el archivo .env");
}
// Definir constantes de entorno
foreach($env as $key=>$value) {
    if(!defined($key)) {
        define($key,$value);
    }
    putenv("$key=$value");
    $_ENV[$key]=$value;
}
// Entorno actual
if(!defined('APP_ENV')){
    define('APP_ENV', $env['APP_ENV'] ?? 'production');
}