<?php
/**
 * maquinas_recreativas - Core Router
 * 
 * Clase para el enrutamiento de peticiones HTTP.
 * 
 * @package maquinas_recreativas\Core
 * @author Tu Equipo
 * @version 1.0
 */

namespace maquinas_recreativas\Core;

class Router
{
    private array $routes = [];
    private array $patterns = [
        ':uuid' => '([a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12})',
        ':id' => '(\d+)',
        ':slug' => '([A-Za-z0-9-]+)', 
        ':any' => '([^/]+)',
        ':estado' => '([A-Za-z]+)'
    ];
    
    /**
     * Añade una ruta
     * 
     * @param string $method Método HTTP
     * @param string $path Patrón de ruta
     * @param callable|array $handler Controlador/método
     * @param array $middleware Middlewares específicos
     */
    public function add(string $method, string $path, $handler, array $middleware = []): void
    {
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'pattern' => $this->compilePattern($path),
            'handler' => $handler,
            'middleware' => $middleware
        ];
    }
    
    /**
     * Compila un patrón de ruta a regex
     * 
     * @param string $path
     * @return string
     */
    private function compilePattern(string $path): string
    {
        $pattern = preg_quote($path, '#');
        
        // Reemplazar placeholders
        foreach ($this->patterns as $placeholder => $regex) {
            $pattern = str_replace(preg_quote($placeholder, '#'), $regex, $pattern);
        }
        
        return '#^' . $pattern . '$#';
    }
    
    /**
     * Busca una ruta que coincida
     * 
     * @param string $method
     * @param string $uri
     * @return array|null
     */
    public function match(string $method, string $uri): ?array
    {
        foreach ($this->routes as $route) {
            if ($route['method'] !== strtoupper($method)) {
                continue;
            }
            
            if (preg_match($route['pattern'], $uri, $matches)) {
                // Extraer parámetros
                array_shift($matches); // Quitar la coincidencia completa
                
                return [
                    'handler' => $route['handler'],
                    'middleware' => $route['middleware'],
                    'params' => $matches
                ];
            }
        }
        
        return null;
    }
    
    /**
     * Añade ruta GET
     */
    public function get(string $path, $handler, array $middleware = []): void
    {
        $this->add('GET', $path, $handler, $middleware);
    }
    
    /**
     * Añade ruta POST
     */
    public function post(string $path, $handler, array $middleware = []): void
    {
        $this->add('POST', $path, $handler, $middleware);
    }
    
    /**
     * Añade ruta PUT
     */
    public function put(string $path, $handler, array $middleware = []): void
    {
        $this->add('PUT', $path, $handler, $middleware);
    }
    
    /**
     * Añade ruta DELETE
     */
    public function delete(string $path, $handler, array $middleware = []): void
    {
        $this->add('DELETE', $path, $handler, $middleware);
    }
    
    /**
     * Añade ruta PATCH
     */
    public function patch(string $path, $handler, array $middleware = []): void
    {
        $this->add('PATCH', $path, $handler, $middleware);
    }
}