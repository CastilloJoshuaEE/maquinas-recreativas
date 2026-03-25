<?php
/**
 * maquinas_recreativas - Authentication Middleware
 * 
 * Verifica que el usuario esté autenticado para acceder a rutas privadas.
 * 
 * @package maquinas_recreativas\Middleware
 * @author Tu Equipo
 * @version 1.0
 */
namespace maquinas_recreativas\Middleware;
use maquinas_recreativas\Core\Request;
use maquinas_recreativas\Core\Response;
class AuthMiddleware{
    private array $publicRoutes=[
        '/health',
        '/test-db',
        '/usuario/login',
        '/usuario/register',
        '/usuario/buscar-email',
        '/usuario/recuperar-contrasena',
        '/usuario/recuperar-usuario'

    ];
    /**
     * Maneja la petición
     * 
     * @param Request $request
     * @param callable $next
     * @return Response|null
     */
    public function handle(Request $request, callable $next):?Response{
        $path=$request->getPath();
        // Verificar si es ruta pública
        if($this->isPublicRoute($path)){
            return $next($request);
        }
        // Verificar autenticación
        if(!isset($_SESSION['ID_Usuario'])){
            $response = new Response();
            $response ->json([
                'success'=> false,
                'message'=> 'No autorizado - Debe iniciar sesión'
            ], 401);
            return $response;
        }
        // Añadir usuario a la request para uso posterior
        $request->user=[
            'id'=> $_SESSION['ID_Usuario'],
            'usuario_asignado'=>$_SESSION['usuario_asignado'],
            'rol'=>$_SESSION['rol']??null
        ];
        return $next($request);
    }    
    /**
     * Verifica si una ruta es pública
     * 
     * @param string $path
     * @return bool
     */
    private function isPublicRoute(string $path):bool{
        // Coincidencia exacta
        if(in_array($path, $this->publicRoutes)){
            return true;
        }
        // Coincidencia por patrón (ej: /usuario/profile/:uuid)
        foreach ($this->publicRoutes as $route) {
            if (strpos($route, ':') !== false) {
                $pattern = str_replace(':uuid', '[a-f0-9-]+', preg_quote($route, '#'));
                if (preg_match('#^' . $pattern . '$#', $path)) {
                    return true;
                }
            }
        }
        
        return false;        
    }    
}