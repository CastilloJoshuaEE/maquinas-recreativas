<?php
/**
 * RecreaSys - JSON Response Middleware
 * 
 * Asegura que todas las respuestas sean JSON válido.
 * 
 * @package RecreaSys\Middleware
 * @author Tu Equipo
 * @version 1.0
 */

namespace RecreaSys\Middleware;

use RecreaSys\Core\Request;
use RecreaSys\Core\Response;

class JsonResponseMiddleware
{
    /**
     * Maneja la petición
     * 
     * @param Request $request
     * @param callable $next
     * @return Response|null
     */
    public function handle(Request $request, callable $next): ?Response
    {
        $response = $next($request);
        
        // Si no hay respuesta, crear una por defecto
        if (!$response) {
            $response = new Response();
        }
        
        // Asegurar Content-Type JSON si no está definido
        // (evitar sobrescribir si ya se estableció otro tipo)
        $hasContentType = false;
        foreach ($response->headers as $name => $value) {
            if (strtolower($name) === 'content-type') {
                $hasContentType = true;
                break;
            }
        }
        
        if (!$hasContentType && !($response instanceof Response && $response->content !== null)) {
            $response->header('Content-Type', 'application/json; charset=utf-8');
        }
        
        return $response;
    }
}