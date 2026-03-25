<?php
/**
 * maquinas_recreativas - Session Configuration
 * 
 * Configura los parámetros de sesión según el entorno.
 * 
 * @package maquinas_recreativas\Bootstrap
 * @author Tu Equipo
 * @version 1.0
 */
// Determinar si la conexión en HTTPS
$isHttps = (
    (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
    (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
);

$isLocalhost = (
    isset($_SERVER['HTTP_HOST']) && (
        $_SERVER['HTTP_HOST'] === 'localhost' ||
        $_SERVER['HTTP_HOST'] === '127.0.0.1'
    )
);
// Configuración de sesión
init_set('session.use_only_cookies','1');
init_set('session.use_strict_mode','1');
init_set('session.cookie_httponly','1');
init_set('session.cookie_samesite','Strict');
init_set('session.gc_maxlifetime',3600); // 1 hora
init_set('session.cookie_lifetime', '0'); // Hasta cerrar el navegador
$secureCookie =$isHttps && !$isLocalhost;
/*
 * Si usas:

* http://localhost

* los navegadores nunca enviarán cookies Secure.

* Por lo tanto:

* Secure = true

* solo funciona con

 * https://localhost
 */
session_set_cookie_params([
    'lifetime'=>0,
    'path'=>'/',
    'domain'=> '',
    'secure'=> $secureCookie,
    'httponly'=> true,
    'samesite'=> 'Strict'
]);
// Iniciar sesión
if(session_status()===PHP_SESSION_NONE){
    session_start();
}
// Regenerar ID de sesión periódicamente para previenir fijación
if(!isset($_SESSION['_created'])){
    $_SESSION['_created'] = time();
    session_regenerate_id(true);
} elseif (time() - $_SESSION['_created'] > 1800) {
    session_regenerate_id(true);
    $_SESSION['_created'] = time();
}