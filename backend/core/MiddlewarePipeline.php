<?php
/**
 * maquinas_recreativas - Core Middleware Pipeline
 * 
 * Implementa el pipeline para ejecutar middlewares en cadena.
 * 
 * @package maquinas_recreativas\Core
 * @author Tu Equipo
 * @version 1.0
 */

namespace maquinas_recreativas\Core;

class MiddlewarePipeline
{
    private array $middlewares = [];
    
    /**
     * Añade un middleware al pipeline
     * 
     * @param string $name Identificador
     * @param string $class Clase del middleware
     * @return self
     */
    public function add(string $name, string $class): self
    {
        $this->middlewares[$name] = $class;
        return $this;
    }
    
    /**
     * Elimina un middleware
     * 
     * @param string $name
     * @return self
     */
    public function remove(string $name): self
    {
        unset($this->middlewares[$name]);
        return $this;
    }
    
    /**
     * Ejecuta el pipeline
     * 
     * @param Request $request
     * @param callable $final Handler final
     * @return void
     */
    public function handle(Request $request, callable $final): void
    {
        $pipeline = array_reverse($this->middlewares);
        
        $next = $final;
        
        foreach ($pipeline as $middlewareClass) {
            $next = function($request) use ($middlewareClass, $next) {
                $middleware = $this->resolveMiddleware($middlewareClass);
                return $middleware->handle($request, $next);
            };
        }
        
        $next($request);
    }
    
    /**
     * Resuelve una instancia de middleware
     * 
     * @param string $class
     * @return object
     * @throws \RuntimeException
     */
 private function resolveMiddleware(string $class): object
{
    // ── Manejar shorthand "role:X" ──────────────────────────────────
    if (str_starts_with($class, 'role:')) {
        $roles = explode(',', substr($class, 5)); // soporta "role:Admin,Logistica"
        return new \maquinas_recreativas\Middleware\RoleMiddleware($roles);
    }

    // ── Resto del comportamiento original ───────────────────────────
    global $container;

    if (isset($container[$class])) {
        return $container[$class];
    }

    if (class_exists($class)) {
        return new $class();
    }

    throw new \RuntimeException("Middleware no encontrado: {$class}");
}
}