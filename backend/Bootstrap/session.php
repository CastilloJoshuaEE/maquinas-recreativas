<?php
/**
 * backend/bootstrap/session.php
 * maquinas_recreativas - Session Configuration
 * 
 * Configura los parámetros de sesión según el entorno.
 * Usa el handler nativo de PHP (archivos) como fallback cuando Redis no está disponible.
 * 
 * @package maquinas_recreativas\Bootstrap
 * @author Tu Equipo
 * @version 1.0
 */

// Determinar si la conexión es HTTPS
$isHttps = (
    (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
    (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
);

$isLocalhost = (
    isset($_SERVER['HTTP_HOST']) && (
        str_contains($_SERVER['HTTP_HOST'], 'localhost') ||
        str_contains($_SERVER['HTTP_HOST'], '127.0.0.1')
    )
);

// Configuración de sesión
ini_set('session.use_only_cookies', '1');
ini_set('session.use_strict_mode', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.gc_maxlifetime', 7200);
ini_set('session.cookie_lifetime', '0');

// SameSite=Lax permite envío en peticiones cross-site iniciadas por el usuario
$sameSite = $isHttps && !$isLocalhost ? 'None' : 'Lax';
$secureCookie = $isHttps && !$isLocalhost;

session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'domain'   => '',
    'secure'   => $secureCookie,
    'httponly' => true,
    'samesite' => $sameSite
]);

// Usar el handler nativo de sesiones (archivos)
// Si Redis está disponible, se configurará en redis.php
ini_set('session.save_handler', 'files');

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

// Si la sesión no se inició, intentar con el handler de archivos
if (session_status() === PHP_SESSION_NONE) {
    // Forzar el handler de archivos
    ini_set('session.save_handler', 'files');
    session_start();
}

// Regenerar ID de sesión periódicamente
if (!isset($_SESSION['_created'])) {
    $_SESSION['_created'] = time();
    if (session_status() === PHP_SESSION_ACTIVE) {
        @session_regenerate_id(true);
    }
} elseif (time() - $_SESSION['_created'] > 1800) {
    if (session_status() === PHP_SESSION_ACTIVE) {
        @session_regenerate_id(true);
    }
    $_SESSION['_created'] = time();
}
 /*
 * Si usas:

* http://localhost

* los navegadores nunca enviarán cookies Secure.

* Por lo tanto:

* Secure = true

* solo funciona con

 * https://localhost
 */
