<?php
/**
 * backend/bootstrap/cors.php
 * maquinas_recreativas - CORS Configuration
 *
 * Fuente unica de verdad para configuracion CORS.
 * Cualquier clase o archivo que necesite origenes permitidos o
 * endpoints publicos debe usar las constantes definidas aqui.
 * No se deben duplicar estas listas en ningun otro archivo.
 *
 * @package maquinas_recreativas\Bootstrap
 * @author Tu Equipo
 * @version 1.0
 */

if (!defined('CORS_ALLOWED_ORIGINS')) {
    define('CORS_ALLOWED_ORIGINS', [
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
    ]);
}

if (!defined('CORS_PUBLIC_ENDPOINTS')) {
    define('CORS_PUBLIC_ENDPOINTS', [
'/api/public/health',
        '/api/public/test-db',
        '/api/public/usuario/login',
        '/api/public/usuario/register',
        '/api/public/usuario/buscar-email',
        '/api/public/usuario/recuperar-contrasena',
        '/api/public/usuario/recuperar-usuario',
            '/api/public/usuario/tecnicos/Ensamblador',
    '/api/public/usuario/tecnicos/Comprobador',
    '/api/public/usuario/tecnicos/Mantenimiento',
'/api/public/usuarios/por-tipo',
    '/api/public/reportes/crear',
    '/api/public/maquina/all',
        '/api/public/reset-rate-limits',  
    ]);
}

/**
 * Aplica las reglas CORS segun la peticion.
 * Se mantiene por compatibilidad para codigo que la invoque directamente.
 * El flujo principal de la aplicacion usa App::handleCors() y CorsMiddleware,
 * ambos alimentados por las constantes definidas en este archivo.
 *
 * @param string $requestUri URI de la peticion
 * @param string $requestMethod Metodo HTTP
 * @return bool True si la peticion puede continuar, false si se detiene
 */
function handleCors(string $requestUri, string $requestMethod): bool
{
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

    if (in_array($requestUri, CORS_PUBLIC_ENDPOINTS, true)) {
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

    if (!$origin) {
        header("Access-Control-Allow-Origin: http://localhost:8000");
    } elseif (in_array($origin, CORS_ALLOWED_ORIGINS, true)) {
        header("Access-Control-Allow-Origin: $origin");
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Max-Age: 86400");
        header("Vary: Origin");
    } else {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'Origen no permitido'
        ]);
        exit();
    }

    if ($requestMethod === 'OPTIONS') {
        http_response_code(200);
        exit();
    }

    return true;
}