<?php
/**
 * RecreaSys - Rate Limit Middleware
 * 
 * Aplica limitación de tasa a las peticiones.
 * 
 * @package RecreaSys\Middleware
 * @author Tu Equipo
 * @version 1.0
 */

namespace RecreaSys\Middleware;

use RecreaSys\Core\Request;
use RecreaSys\Core\Response;
use RecreaSys\Infrastructure\Security\RateLimiter;

class RateLimitMiddleware
{
    private array $strictRateLimitRoutes=[
        '/usuario/login',
        '/usuario/register'
    ];
    private RateLimiter $limiter;
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->rateLimiter = RateLimiter::getInstance();
    }
    /**
     * Maneja la petición
     * 
     * @param Request $request
     * @param callable $next
     * @return Response|null
     */        
    public function handle(Request $request, callable $next):?Response{
        $path = $request->getPath();
        $clientKey = $request->getClientIp();
        // Configurar límites
        if(in_array($path, $this->strictRateLimitRoutes)){
            $maxRequests = $request->isLocal()?60:5;
            $timeWindow = $request->isLocal()?60:300;
        } else{
            $maxRequests = $request->isLocal()?300:60;
            $timeWindow = 60;
        }
        // Desactivar en tests
        if(defined('TEST_ENVIRONMENT')&& TEST_ENVIRONMENT===true){
            $response = $next($request);
            if($response instanceof Response){
                $response->header('X-RateLimit-Limit',(string)$maxRequests)->header('X-RateLimit-Remaining', '9999');
            }
            return $response;
        }
        // Verificar rate limit
        if(!$this->rateLimiter->check($clientKey, $maxRequests, $timeWindow)){
            $response= new Response();
            $response->json([
                'success'=>false,
                'message'=> 'Demasiadas solicitudes. Intente nuevamente más tarde.'

            ],429);
            return $response;
        }
        // Ejecutar siguiente middleware
        $response = $next($request);
        // Añadir headers informativos
        if($response instanceof Response){
            $remaining = $this->rateLimiter->getRemaining($clientKey, $maxRequests, $timeWindow);
            $response->header('X-RateLimit-Limit',(string)$maxRequests)->header('X-RateLimit-Remaining',(string)$remaining);
        }
        return $response;
    }
}