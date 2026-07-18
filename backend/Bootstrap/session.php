<?php
/**
 * backend/bootstrap/session.php
 * maquinas_recreativas - Session Configuration
 * 
 * Configura los parámetros de sesión según el entorno.
 * Usa el handler nativo de PHP (archivos) para sesiones.
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

// =============================================
// CONFIGURACIÓN DE SESIÓN
// =============================================

// Forzar el uso de archivos para sesiones (no Redis)
ini_set('session.save_handler', 'files');
ini_set('session.save_path', sys_get_temp_dir());

// Configuración de seguridad
ini_set('session.use_only_cookies', '1');
ini_set('session.use_strict_mode', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.gc_maxlifetime', 7200);
ini_set('session.cookie_lifetime', '0');

// SameSite
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

// =============================================
// INICIAR SESIÓN
// =============================================

// Verificar si la sesión ya está activa
if (session_status() === PHP_SESSION_ACTIVE) {
    // Ya hay una sesión activa, no hacer nada
} else {
    // Intentar iniciar sesión
    session_start();
}

// Si falló, asegurar que se use el handler de archivos
if (session_status() !== PHP_SESSION_ACTIVE) {
    ini_set('session.save_handler', 'files');
    ini_set('session.save_path', sys_get_temp_dir());
    session_start();
}

// =============================================
// REGENERAR ID DE SESIÓN PERIÓDICAMENTE
// =============================================

if (session_status() === PHP_SESSION_ACTIVE) {
    if (!isset($_SESSION['_created'])) {
        $_SESSION['_created'] = time();
        session_regenerate_id(true);
    } elseif (time() - $_SESSION['_created'] > 1800) {
        session_regenerate_id(true);
        $_SESSION['_created'] = time();
    }
}

// =============================================
// FUNCIÓN DE AYUDA PARA SESIÓN
// =============================================

if (!function_exists('session_is_active')) {
    function session_is_active(): bool
    {
        return session_status() === PHP_SESSION_ACTIVE;
    }
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
