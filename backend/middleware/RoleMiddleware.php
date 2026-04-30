<?php
/**
 * maquinas_recreativas - Role Middleware
 * 
 * Verifica que el usuario tenga el rol necesario.
 * 
 * @package maquinas_recreativas\Middleware
 * @author Tu Equipo
 * @version 1.0
 */
namespace maquinas_recreativas\Middleware;
use maquinas_recreativas\Core\Request;
use maquinas_recreativas\Core\Response;
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