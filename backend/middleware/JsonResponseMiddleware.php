<?php
/**
 * maquinas_recreativas - JSON Response Middleware
 * 
 * Asegura que todas las respuestas sean JSON válido.
 * 
 * @package maquinas_recreativas\Middleware
 * @author Tu Equipo
 * @version 1.0
 */

namespace maquinas_recreativas\Middleware;

use maquinas_recreativas\Core\Request;
use maquinas_recreativas\Core\Response;

class JsonResponseMiddleware
{
    public function handle(Request $request, callable $next): ?Response
    {
        $response = $next($request);
        
        if (!$response) {
            $response = new Response();
        }
        
        // Usar el nuevo método getHeaders()
        $hasContentType = false;
        foreach ($response->getHeaders() as $name => $value) {
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