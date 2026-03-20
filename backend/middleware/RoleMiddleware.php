<?php
/**
 * RecreaSys - Role Middleware
 * 
 * Verifica que el usuario tenga el rol necesario.
 * 
 * @package RecreaSys\Middleware
 * @author Tu Equipo
 * @version 1.0
 */
namespace RecreaSys\Middleware;
use RecreaSys\Core\Request;
use RecreaSys\Core\Response;
class RoleMiddleware{
    private array $roles;
    /**
     * Constructor
     * 
     * @param array|string $roles Roles permitidos
     */
    public function __construct($roles){
        $this->roles = is_array( $roles ) ? $roles : [$roles];

    }   
    /**
     * Maneja la petición
     * 
     * @param Request $request
     * @param callable $next
     * @return Response|null
     */ 
    public function handle(Request $request, callable $next): ?Response
        {
            $userRole = $_SESSION['rol'] ?? null;
            
            if (!$userRole || !in_array($userRole, $this->roles)) {
                $response = new Response();
                $response->json([
                    'success' => false,
                    'message' => 'No tiene permisos suficientes'
                ], 403);
                return $response;
            }
            
            return $next($request);
        }
}