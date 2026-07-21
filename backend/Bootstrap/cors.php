<?php
/**
 * backend/bootstrap/cors.php
 * maquinas_recreativas - CORS Configuration
 * 
 * Maneja las reglas CORS (Cross-Origin Resource Sharing).
 * 
 * @package maquinas_recreativas\Bootstrap
 * @author Tu Equipo
 * @version 1.0
 */

// Orígenes permitidos
$allowedOrigins = [
        'http://localhost:4200',
        'http://localhost:8000',
        'http://localhost:3000',
            'http://127.0.0.1:4200',
        'http://127.0.0.1:8000',
        'http://127.0.0.1',
        'http://localhost',
        'http://127.0.0.1:8080',
        'http://localhost:8080',
        'https://maquinas-recreativas.vercel.app',
        'https://maquinas-recreativas1.onrender.com',
];

// Endpoints públicos que no requieren autenticación
$publicEndpoints = [
    '/health',
    '/test-db',
    '/usuario/login',
    '/usuario/register',
    '/usuario/buscar-email',
    '/usuario/recuperar-contrasena',
    '/usuario/recuperar-usuario'
];

/**
 * Aplica las reglas CORS según la petición
 * 
 * @param string $requestUri URI de la petición
 * @param string $requestMethod Método HTTP
 * @return bool True si la petición puede continuar, false si se detiene
 */
function handleCors(string $requestUri, string $requestMethod): bool {
    global $allowedOrigins, $publicEndpoints;
    
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    
    // Endpoints públicos tienen CORS más permisivo
    if (in_array($requestUri, $publicEndpoints)) {
        header("Access-Control-Allow-Origin: " . ($origin ?: 'http://localhost:8000'));
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type");
        header("Vary: Origin");
        
        if ($requestMethod === 'OPTIONS') {
            http_response_code(200);
            exit();
        }
        return true;
    }
    
    // Para endpoints privados
    if (!$origin) {
        header("Access-Control-Allow-Origin: http://localhost:8000");
    } elseif (in_array($origin, $allowedOrigins, true)) {
        header("Access-Control-Allow-Origin: $origin");
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Max-Age: 86400");
        header("Vary: Origin");
    } else {
        // Origen no permitido
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'Origen no permitido'
        ]);
        exit();
    }
    
    // Manejar preflight
    if ($requestMethod === 'OPTIONS') {
        http_response_code(200);
        exit();
    }
    
    return true;
}