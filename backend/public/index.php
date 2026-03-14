<?php
// Permitir el origen específico del frontend
$allowedOrigins = [
    'http://localhost:5173',
    'http://127.0.0.1:5173',
    'http://localhost:8000',
'https://prototipo-maquinas.vercel.app'
];

if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $allowedOrigins)) {
    header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);
}
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

header("Content-Type: application/json");

/**
 * Procesamiento de la URL
 */
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// CORRECCIÓN: La ruta base depende de cómo se ejecuta el servidor
// Cuando usas php -S localhost:8000 -t public, la raíz es /, no /maquinas-recreativas/backend/public
$basePath = ''; // Vacío porque el servidor PHP sirve desde la raíz

$basePath = '/api/public';

$apiRoute = str_replace($basePath, '', $requestUri);
$apiRoute = str_replace('/index.php', '', $apiRoute);

// Si la ruta está vacía, establecer como '/'
if (empty($apiRoute)) {
    $apiRoute = '/';
}

// CORRECCIÓN: Usar __DIR__ para construir la ruta absoluta
$routesFile = __DIR__ . '/../routes.php';

if (!file_exists($routesFile)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Archivo routes.php no encontrado: ' . $routesFile]);
    exit();
}

require $routesFile;
routeRequest($apiRoute, $_SERVER['REQUEST_METHOD']);