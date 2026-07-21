<?php
namespace maquinas_recreativas\Middleware;

use maquinas_recreativas\Core\Request;
use maquinas_recreativas\Core\Response;

class CorsMiddleware
{
    /**
     * Usa CORS_ALLOWED_ORIGINS definida en backend/bootstrap/cors.php.
     * No define su propia lista de origenes.
     */
    public function handle(Request $request, callable $next): ?Response
    {
        $origin = $request->header('ORIGIN', '');
        $allowedOrigins = defined('CORS_ALLOWED_ORIGINS') ? CORS_ALLOWED_ORIGINS : [];

        if (in_array($origin, $allowedOrigins, true) || !$origin) {
            header("Access-Control-Allow-Origin: " . ($origin ?: 'http://localhost:4200'));
        }

        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

        if ($request->getMethod() === 'OPTIONS') {
            (new Response())->status(204)->send();
            return null;
        }

        return $next($request);
    }
}