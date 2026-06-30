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
    
    private function compilePattern(string $path): string
    {
        $pattern = preg_quote($path, '#');
        
        foreach ($this->patterns as $placeholder => $regex) {
            $pattern = str_replace(preg_quote($placeholder, '#'), $regex, $pattern);
        }
        
        return '#^' . $pattern . '$#';
    }
    
    public function match(string $method, string $uri): ?array
    {
        error_log("=== Router::match ===");
        error_log("Buscando: " . $method . " " . $uri);
        error_log("Total routes: " . count($this->routes));
        
        foreach ($this->routes as $index => $route) {
            error_log("Route " . $index . ": " . $route['method'] . " " . $route['path'] . " -> " . $route['pattern']);
            
            if ($route['method'] !== strtoupper($method)) {
                continue;
            }
            
            if (preg_match($route['pattern'], $uri, $matches)) {
                error_log("MATCH FOUND! Route: " . $route['path']);
                array_shift($matches);
                return [
                    'handler' => $route['handler'],
                    'middleware' => $route['middleware'],
                    'params' => $matches
                ];
            }
        }
        
        error_log("No match found");
        return null;
    }
    
    public function get(string $path, $handler, array $middleware = []): void
    {
        $this->add('GET', $path, $handler, $middleware);
    }
    
    public function post(string $path, $handler, array $middleware = []): void
    {
        $this->add('POST', $path, $handler, $middleware);
    }
    
    public function put(string $path, $handler, array $middleware = []): void
    {
        $this->add('PUT', $path, $handler, $middleware);
    }
    
    public function delete(string $path, $handler, array $middleware = []): void
    {
        $this->add('DELETE', $path, $handler, $middleware);
    }
    
    public function patch(string $path, $handler, array $middleware = []): void
    {
        $this->add('PATCH', $path, $handler, $middleware);
    }
}