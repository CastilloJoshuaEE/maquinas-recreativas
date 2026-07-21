<?php
namespace maquinas_recreativas\Middleware;

use maquinas_recreativas\Core\Request;
use maquinas_recreativas\Core\Response;

class CorsMiddleware
{
    private array $allowedOrigins = [
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

    private array $publicEndpoints = [
        '/health',
        '/test-db'
    ];

public function handle(Request $request, callable $next): ?Response
{
    $origin = $request->header('ORIGIN', '');
    
    // Permitir origen
    if (in_array($origin, $this->allowedOrigins, true) || !$origin) {
        header("Access-Control-Allow-Origin: " . ($origin ?: 'http://localhost:4200'));
    }
    
    // Permitir credenciales para cookies de sesión
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
    
    // Manejar preflight
    if ($request->getMethod() === 'OPTIONS') {
        (new Response())->status(204)->send();
        return null;
    }
    
    return $next($request);
}
}