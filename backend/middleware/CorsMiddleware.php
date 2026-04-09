<?php
namespace maquinas_recreativas\Middleware;

use maquinas_recreativas\Core\Request;
use maquinas_recreativas\Core\Response;

class CorsMiddleware
{
    private array $allowedOrigins = [
        'http://localhost:4200',
        'http://localhost:8000',
        'http://127.0.0.1',
        'http://localhost',
        'http://127.0.0.1:8080',
        'http://localhost:8080'
    ];

    private array $publicEndpoints = [
        '/health',
        '/test-db'
    ];

    public function handle(Request $request, callable $next): ?Response
    {
        $origin = $request->header('ORIGIN', '');
        $method = $request->getMethod();
        $path   = $request->getPath();

        // Headers CORS comunes (se aplican siempre si el origen es válido o es localhost)
        $resolvedOrigin = '';
        if (!$origin) {
            $resolvedOrigin = 'http://localhost:4200';
        } elseif (in_array($origin, $this->allowedOrigins, true)) {
            $resolvedOrigin = $origin;
        } else {
            $response = new Response();
            $response->json(['success' => false, 'message' => 'Origen no permitido'], 403);
            return $response;
        }

        header("Access-Control-Allow-Origin: {$resolvedOrigin}");
        // CRÍTICO: permitir credentials para que Angular envíe la cookie de sesión
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS");
        // CORREGIDO: incluir Authorization y Content-Type en todos los endpoints (incluidos públicos)
        header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
        header("Access-Control-Max-Age: 86400");
        header("Vary: Origin");

        // Manejar preflight
        if ($method === 'OPTIONS') {
            $response = new Response();
            $response->status(204)->send();
            return $response;
        }

        return $next($request);
    }
}