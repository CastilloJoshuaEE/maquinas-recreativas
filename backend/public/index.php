<?php

// =============================================
// CONFIGURACIÓN PHP
// =============================================
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Detectar si la conexión es HTTPS
$isHttps = (
    (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
    ($_SERVER['SERVER_PORT'] == 443)
);
$isLocalhost = (
    $_SERVER['HTTP_HOST'] === 'localhost' ||
    $_SERVER['HTTP_HOST'] === '127.0.0.1'
);
// Configuración segura de sesiones
ini_set('session.use_only_cookies', 1);
ini_set('session.gc_maxlifetime', 3600);

$secureCookie = $isHttps && !$isLocalhost;
/**
 * 
 * 
 * Si usas:

* http://localhost

* los navegadores nunca enviarán cookies Secure.

* Por lo tanto:

* Secure = true

* solo funciona con

 * https://localhost
 */

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => $secureCookie,
    'httponly' => true,
    'samesite' => 'Strict'
]);
// =============================================
// INICIAR SESIÓN
// =============================================
session_start();
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
require_once __DIR__ . '/../helper/RateLimiter.php';


// =============================================
// CONFIGURACIÓN CORS
// =============================================

$allowedOrigins = [
    'http://localhost:5173',
    'http://localhost:8000'
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

if (in_array($origin, $allowedOrigins, true)) {

    header("Access-Control-Allow-Origin: " . $origin);
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
    header("Access-Control-Max-Age: 86400");
    header("Content-Type: application/json; charset=UTF-8");

} else {

    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Origen no permitido'
    ]);
    exit();
}

// Manejar preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}


// =============================================
// OBTENER RUTA DE LA API
// =============================================

$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Ajusta según tu despliegue
$basePath = '/api/public';

$apiRoute = str_replace($basePath, '', $requestUri);
$apiRoute = str_replace('/index.php', '', $apiRoute);

if (empty($apiRoute)) {
    $apiRoute = '/';
}


// =============================================
// RATE LIMITING
// =============================================

// Rutas con límite estricto
$publicRateLimitRoutes = [
    '/usuario/login',
    '/usuario/register'
];

$rateLimiter = RateLimiter::getInstance();

$clientIP = $_SERVER['HTTP_X_FORWARDED_FOR']
    ?? $_SERVER['REMOTE_ADDR']
    ?? 'unknown';

$clientKey = $clientIP;

// Configuración de límites
if (in_array($apiRoute, $publicRateLimitRoutes)) {

    $maxRequests = 5;
    $timeWindow = 300; // 5 minutos

} else {

    $maxRequests = 60;
    $timeWindow = 60; // 1 minuto
}

if (!$rateLimiter->check($clientKey, $maxRequests, $timeWindow)) {

    http_response_code(429);

    echo json_encode([
        'success' => false,
        'message' => 'Demasiadas solicitudes. Intente nuevamente más tarde.'
    ]);

    exit();
}

// Headers informativos
header('X-RateLimit-Limit: ' . $maxRequests);
header('X-RateLimit-Remaining: ' . $rateLimiter->getRemaining($clientKey, $maxRequests, $timeWindow));


// =============================================
// RUTAS PÚBLICAS
// =============================================

$publicRoutes = [
    '/health',
    '/test-db',
    '/usuario/login',
    '/usuario/register',
    '/usuario/buscar-email',
    '/usuario/recuperar-contrasena',
    '/usuario/recuperar-usuario'
];


// =============================================
// MIDDLEWARE DE AUTENTICACIÓN
// =============================================

function requireAuth($route, $publicRoutes) {

    if (in_array($route, $publicRoutes)) {
        return true;
    }

    if (!isset($_SESSION['ID_Usuario'])) {

        http_response_code(401);

        echo json_encode([
            'success' => false,
            'message' => 'No autorizado - Debe iniciar sesión'
        ]);

        exit();
    }

    return true;
}

requireAuth($apiRoute, $publicRoutes);


// =============================================
// CARGAR ROUTES
// =============================================

$routesFile = __DIR__ . '/../routes.php';

if (!file_exists($routesFile)) {

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Archivo routes.php no encontrado: ' . $routesFile
    ]);

    exit();
}

require $routesFile;


// =============================================
// EJECUTAR RUTA
// =============================================

routeRequest($apiRoute, $_SERVER['REQUEST_METHOD']);