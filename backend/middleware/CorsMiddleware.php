<?php
/**
 * maquinas_recreativas - CORS Middleware
 * 
 * Maneja las reglas CORS.
 * 
 * @package maquinas_recreativas\Middleware
 * @author Tu Equipo
 * @version 1.0
 */

namespace maquinas_recreativas\Middleware;

use maquinas_recreativas\Core\Request;
use maquinas_recreativas\Core\Response;

class CorsMiddleware
{
    private array $allowedOrigins = [
        'http://localhost:5173',
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
    
    /**
     * Maneja la petición
     * 
     * @param Request $request
     * @param callable $next
     * @return Response|null
     */
    public function handle(Request $request, callable $next): ?Response
    {
        $origin = $request->header('ORIGIN', '');
        $method = $request->getMethod();
        $path = $request->getPath();
        
        // Endpoints públicos tienen CORS más permisivo
        if (in_array($path, $this->publicEndpoints)) {
            header("Access-Control-Allow-Origin: " . ($origin ?: 'http://localhost:8000'));
            header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
            header("Access-Control-Allow-Headers: Content-Type");
            header("Vary: Origin");
            
            if ($method === "OPTIONS") {
                $response = new Response();
                $response->status(200)->send();
                return $response;
            }
            return $next($request);
        }
        
        // Para endpoints privados
        if (!$origin) {
            header("Access-Control-Allow-Origin: http://localhost:8000");
        } elseif (in_array($origin, $this->allowedOrigins, true)) {
            header("Access-Control-Allow-Origin: $origin");
            header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS");
            header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
            header("Access-Control-Allow-Credentials: true");
            header("Access-Control-Max-Age: 86400");
            header("Vary: Origin");
        } else {
            // Origen no permitido
            $response = new Response();
            $response->json([
                'success' => false,
                'message' => 'Origen no permitido'
            ], 403);
            return $response;
        }
        
        // Manejar preflight
        if ($method === 'OPTIONS') {
            $response = new Response();
            $response->status(200)->send();
            return $response;
        }
        
        return $next($request);
    }
}