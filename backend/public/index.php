<?php
// ---------------------------
// CONFIGURACIÓN CORS
// ---------------------------
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$allowedOrigins = [
    'http://localhost:5173',
    'http://localhost:8000',
    'https://prototipo-maquinas.vercel.app'
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

if (in_array($origin, $allowedOrigins, true)) {
    header("Access-Control-Allow-Origin: " . $origin);
}
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept");
header("Content-Type: application/json");

// ---------------------------
// RESPONDER PRE-FLIGHT OPTIONS
// ---------------------------
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}

// ---------------------------
// ENRUTAMIENTO
// ---------------------------

// Obtener la ruta de la solicitud
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Ajusta la base de tu API según el despliegue
// Por ejemplo, en InfinityFree: https://tu-dominio.com/api/public/...
$basePath = '/api/public';
$apiRoute = str_replace($basePath, '', $requestUri);
$apiRoute = str_replace('/index.php', '', $apiRoute);

// Si la ruta queda vacía, poner "/"
if (empty($apiRoute)) {
    $apiRoute = '/';
}

// ---------------------------
// CARGAR ROUTES
// ---------------------------
$routesFile = __DIR__ . '/../routes.php';
if (!file_exists($routesFile)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Archivo routes.php no encontrado: ' . $routesFile]);
    exit();
}

require $routesFile;

// ---------------------------
// EJECUTAR RUTA
// ---------------------------
routeRequest($apiRoute, $_SERVER['REQUEST_METHOD']);