<?php
/**
 * RecreaSys - Core Router
 * 
 * Clase para el enrutamiento de peticiones HTTP.
 * 
 * @package RecreaSys\Core
 * @author Tu Equipo
 * @version 1.0
 */

namespace RecreaSys\Core;

class Router
{
    private array $routes = [];
    private array $patterns = [
        ':uuid' => '([a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12})',
        ':id' => '(\d+)',
        ':slug' => '([a-z0-9-]+)',
        ':any' => '([^/]+)'
    ];
    
    /**
     * Añade una ruta
     * 
     * @param string $method Método HTTP
     * @param string $path Patrón de ruta
     * @param callable|array $handler Controlador/método
     * @param array $middleware Middlewares específicos
     */
    public function add(string $method, string $path, $handler, array $middleware = []):void{
        $this->routes[]=[
            'method'=> strtoupper($method),
            'path'=> $path,
            'pattern'=>$this->compilePatterns($path),
            'handler'=>$handler,
            'middleware'=> $middleware
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
    public function match(string $method, string $uri):?array{
        foreach($this->routes as $route){
            if($route)
        }
    }    
}    