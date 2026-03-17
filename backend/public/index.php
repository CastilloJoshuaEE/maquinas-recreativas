<?php
ob_start();
ini_set('expose_php', 0);
header_remove("X-Powered-By");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$isHttps = (
    (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
    ($_SERVER['SERVER_PORT'] == 443)
);
$isLocalhost = (
    $_SERVER['HTTP_HOST'] === 'localhost' ||
    $_SERVER['HTTP_HOST'] === '127.0.0.1'
);

ini_set('session.use_only_cookies', 1);
ini_set('session.gc_maxlifetime', 3600);
ini_set('session.cookie_samesite', 'Strict');

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
    'path'     => '/',
    'domain'   => '',
    'secure'   => $secureCookie,
    'httponly' => true,
    'samesite' => 'Strict'
]);

session_start();
securityHeaders();

function securityHeaders() {
    header("Server: SecureServer");
    header("X-Content-Type-Options: nosniff");
    header("X-Frame-Options: DENY");
    header("X-XSS-Protection: 1; mode=block");
    header("Referrer-Policy: no-referrer");
    header("Permissions-Policy: geolocation=(), microphone=(), camera=()");
    header("X-Permitted-Cross-Domain-Policies: none");
    header(
        "Content-Security-Policy: " .
        "default-src 'self'; " .
        "connect-src 'self' http://localhost:5173 https://recreasys.infinityfree.me; " .
        "img-src 'self' data:; " .
        "script-src 'self'; " .
        "style-src 'self'; " .
        "frame-ancestors 'none'; " .
        "base-uri 'self'; " .
        "form-action 'self'; " .
        "object-src 'none'; " .
        "font-src 'self';"
    );
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Pragma: no-cache");
}

if ($isHttps) {
    header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload");
}

require_once __DIR__ . '/../helper/RateLimiter.php';

// =============================================
// OBTENER RUTA DE LA API
// =============================================

$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$basePath   = '/api/public';
$apiRoute   = str_replace($basePath, '', $requestUri);
$apiRoute   = str_replace('/index.php', '', $apiRoute);
$apiRoute   = rtrim($apiRoute, '/');

if ($apiRoute === '') {
    $apiRoute = '/';
}

// =============================================
// ENDPOINT EXCLUSIVO PARA PRUEBAS: /reset-rate-limits
// Solo accesible desde localhost.
// =============================================
if ($apiRoute === '/reset-rate-limits') {
    $clientIP = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $isLocal  = in_array($clientIP, ['127.0.0.1', '::1', 'localhost', '::ffff:127.0.0.1']);

    if (!$isLocal) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
        exit();
    }

    header('Content-Type: application/json');
    $rateLimiter = RateLimiter::getInstance();
    $rateLimiter->resetAll();
    http_response_code(200);
    echo json_encode(['success' => true, 'message' => 'Rate limits reseteados']);
    exit();
}

// =============================================
// CONFIGURACIÓN CORS
// =============================================

$allowedOrigins = [
    'http://localhost:5173',
    'http://localhost:8000',
    'http://127.0.0.1',
    'http://localhost',
    'http://127.0.0.1:8080',
    'http://localhost:8080'
];

$publicEndpoints = ['/health', '/test-db'];

$origin        = $_SERVER['HTTP_ORIGIN'] ?? '';
$requestMethod = $_SERVER['REQUEST_METHOD'];

if (in_array($apiRoute, $publicEndpoints)) {
    header("Access-Control-Allow-Origin: " . ($origin ?: 'http://localhost:8000'));
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type");
    header("Vary: Origin");

    if ($requestMethod === 'OPTIONS') {
        http_response_code(200);
        exit();
    }
} else {
    if (!$origin) {
        header("Access-Control-Allow-Origin: http://localhost:8000");
    } elseif (in_array($origin, $allowedOrigins, true)) {
        header("Access-Control-Allow-Origin: $origin");
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Max-Age: 86400");
        header("Vary: Origin");
    } else {
        sendResponse(['success' => false, 'message' => 'Origen no permitido'], 403);
    }

    if ($requestMethod === 'OPTIONS') {
        http_response_code(200);
        exit();
    }
}

// =============================================
// ARCHIVOS AUTOMÁTICOS DE SCANNERS
// =============================================

if ($apiRoute === '/robots.txt') {
    header("Content-Type: text/plain; charset=utf-8");
    header("X-Content-Type-Options: nosniff");
    echo "User-agent: *\nDisallow: /";
    exit();
}
if ($apiRoute === '/sitemap.xml') {
    header("Content-Type: application/xml; charset=utf-8");
    echo '<?xml version="1.0" encoding="UTF-8"?>';
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>';
    exit();
}
if ($apiRoute === '/favicon.ico') {
    http_response_code(204);
    exit();
}

$blockedScannerRoutes = [
    '/latest/meta-data', '/computeMetadata', '/metadata',
    '/opc', '/openstack', '/actuator'
];
foreach ($blockedScannerRoutes as $blocked) {
    if (str_starts_with($apiRoute, $blocked)) {
        http_response_code(404);
        exit();
    }
}

// =============================================
// RATE LIMITING
// =============================================

$publicRateLimitRoutes = [
    '/usuario/login',
    '/usuario/register'
];

$rateLimiter = RateLimiter::getInstance();

$clientIP  = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$clientKey = $clientIP;

// *** CLAVE: localhost recibe límites más altos para no bloquear las pruebas ***
$isLocalRequest = in_array($clientIP, ['127.0.0.1', '::1', 'localhost', '::ffff:127.0.0.1']);

if (in_array($apiRoute, $publicRateLimitRoutes)) {
    // Producción: 5 req / 5 min  |  Localhost: 60 req / 1 min
    $maxRequests = $isLocalRequest ? 60  : 5;
    $timeWindow  = $isLocalRequest ? 60  : 300;
} else {
    // Producción: 60 req / 1 min  |  Localhost: 300 req / 1 min
    $maxRequests = $isLocalRequest ? 300 : 60;
    $timeWindow  = 60;
}

if (!$rateLimiter->check($clientKey, $maxRequests, $timeWindow)) {
    http_response_code(429);
    echo json_encode([
        'success' => false,
        'message' => 'Demasiadas solicitudes. Intente nuevamente más tarde.'
    ]);
    exit();
}

header('X-RateLimit-Limit: '     . $maxRequests);
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