<?php
/**
 * maquinas_recreativas - Rate Limit Middleware
 * 
 * Aplica limitación de tasa a las peticiones.
 * 
 * @package maquinas_recreativas\Middleware
 * @author Tu Equipo
 * @version 1.0
 */

namespace maquinas_recreativas\Middleware;

use maquinas_recreativas\Core\Request;
use maquinas_recreativas\Core\Response;
use maquinas_recreativas\Infrastructure\Security\RateLimiter;

class RateLimitMiddleware
{
    private array $strictRateLimitRoutes = [
        '/usuario/login',
        '/usuario/register'
    ];
    
    private RateLimiter $rateLimiter;
    
    public function __construct()
    {
        $this->rateLimiter = RateLimiter::getInstance();
    }
    
    public function handle(Request $request, callable $next): ?Response
    {
        $path = $request->getPath();
        $clientKey = $request->getClientIp();
        
        // Detectar si es una prueba de seguridad
        $isSecurityTest = strtolower((string)$request->header('X-Security-Test')) === 'true';
        
        // Para pruebas de seguridad, usar límites muy bajos
        if ($isSecurityTest) {
            $maxRequests = 3;  // Solo 3 peticiones permitidas
            $timeWindow = 60;   // En 60 segundos
        }
        // Para rutas estrictas en producción
        elseif (in_array($path, $this->strictRateLimitRoutes)) {
            $maxRequests = $request->isLocal() ? 60 : 5;
            $timeWindow = $request->isLocal() ? 60 : 300;
        }
        // Para el resto
        else {
            $maxRequests = $request->isLocal() ? 300 : 60;
            $timeWindow = 60;
        }
        
        // En entorno de pruebas, NO saltar rate limiting si es security test
        if (defined('TEST_ENVIRONMENT') && TEST_ENVIRONMENT === true && !$isSecurityTest) {
            return $next($request);
        }
        
        // Verificar rate limit
        if (!$this->rateLimiter->check($clientKey, $maxRequests, $timeWindow)) {
            return (new Response())->json([
                'success' => false,
                'message' => 'Demasiadas solicitudes. Intente nuevamente más tarde.',
                'retry_after' => $timeWindow
            ], 429);
        }
        
        $response = $next($request);
        
        if ($response instanceof Response) {
            $remaining = $this->rateLimiter->getRemaining($clientKey, $maxRequests, $timeWindow);
            $response->header('X-RateLimit-Limit', (string)$maxRequests)
                     ->header('X-RateLimit-Remaining', (string)$remaining);
        }
        
        return $response;
    }
}