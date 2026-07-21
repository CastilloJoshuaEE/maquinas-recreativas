<?php
/**
 * backend/middleware/AuthMiddleware
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

class AuthMiddleware
{
    /**
     * Rutas públicas (sin autenticación)
     * Usa la constante CORS_PUBLIC_ENDPOINTS definida en bootstrap/cors.php
     */
    private array $publicRoutes = [];

    public function __construct()
    {
        //  Cargar rutas públicas desde la constante global
        if (defined('CORS_PUBLIC_ENDPOINTS')) {
            $this->publicRoutes = CORS_PUBLIC_ENDPOINTS;
        } else {
            //  Fallback por si la constante no está definida
            $this->publicRoutes = [
                '/api/public/health',
                '/api/public/test-db',
                '/api/public/usuario/login',
                '/api/public/usuario/register',
                '/api/public/usuario/buscar-email',
                '/api/public/usuario/recuperar-contrasena',
                '/api/public/usuario/recuperar-usuario',
                '/api/public/reset-rate-limits'
            ];
        }
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
        $path = $request->getPath();
        
        //  Verificar si es ruta pública (usa la constante global)
        if ($this->isPublicRoute($path)) {
            return $next($request);
        }
        
        // Verificar autenticación
        if (!isset($_SESSION['ID_Usuario'])) {
            $response = new Response();
            $response->json([
                'success' => false,
                'message' => 'No autorizado - Debe iniciar sesión'
            ], 401);
            return $response;
        }
        
        // Añadir usuario a la request para uso posterior
        $request->user = [
            'id' => $_SESSION['ID_Usuario'],
            'usuario_asignado' => $_SESSION['usuario_asignado'],
            'rol' => $_SESSION['rol'] ?? null
        ];
        
        return $next($request);
    }

    /**
     * Verifica si una ruta es pública
     * 
     * @param string $path
     * @return bool
     */
    private function isPublicRoute(string $path): bool
    {
        //  Verificar en la lista de rutas públicas (desde constante global)
        if (in_array($path, $this->publicRoutes, true)) {
            return true;
        }

        //  Rutas de técnicos son públicas (por ejemplo, listado de técnicos)
        if (strpos($path, '/usuario/tecnicos/') !== false) {
            return true;
        }

        //  Rutas de usuarios por tipo son públicas
        if (strpos($path, '/usuarios/por-tipo') !== false) {
            return true;
        }

        //  Rutas de reportes/crear son públicas
        if (strpos($path, '/reportes/crear') !== false) {
            return true;
        }

        //  Rutas de maquina/all son públicas
        if (strpos($path, '/maquina/all') !== false) {
            return true;
        }

        return false;
    }
}