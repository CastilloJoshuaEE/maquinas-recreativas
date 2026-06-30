<?php
namespace maquinas_recreativas\Core;

use maquinas_recreativas\Core\Router;
use maquinas_recreativas\Core\Request;
use maquinas_recreativas\Core\Response;
use maquinas_recreativas\Core\MiddlewarePipeline;

class App
{
    private static ?self $instance = null;
    private Router $router;
    private Request $request;
    private Response $response;
    private MiddlewarePipeline $pipeline;
    private array $config;

    private function __construct()
    {
        $this->request = new Request();
        $this->response = new Response();
        $this->router = new Router();
        $this->pipeline = new MiddlewarePipeline();
        $this->loadConfig();
        $this->registerMiddleware();
        $this->registerRoutesDirect();
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function loadConfig(): void
    {
        $this->config = [
            'env' => APP_ENV ?? 'production',
            'debug' => (APP_ENV ?? 'production') === 'development',
            'timezone' => date_default_timezone_get()
        ];
    }

    private function registerMiddleware(): void
    {
        $this->pipeline->add('cors', \maquinas_recreativas\Middleware\CorsMiddleware::class);
        $this->pipeline->add('rate-limit', \maquinas_recreativas\Middleware\RateLimitMiddleware::class);
        $this->pipeline->add('auth', \maquinas_recreativas\Middleware\AuthMiddleware::class);
        $this->pipeline->add('json-response', \maquinas_recreativas\Middleware\JsonResponseMiddleware::class);
    }

    /**
     * Registra rutas directamente sin depender de archivos
     */
    private function registerRoutesDirect(): void
    {
        error_log("=== REGISTRANDO RUTAS DIRECTAMENTE ===");
        
        // Ruta health
        $this->router->add(
            'GET',
            '/health',
            [\maquinas_recreativas\Interfaces\Http\Controllers\HealthController::class, 'check'],
            []
        );
        error_log("Ruta registrada: GET /health");
        
        // Ruta test-db
        $this->router->add(
            'GET',
            '/test-db',
            [\maquinas_recreativas\Interfaces\Http\Controllers\HealthController::class, 'testDb'],
            []
        );
        error_log("Ruta registrada: GET /test-db");
        
        // Ruta raíz
        $this->router->add(
            'GET',
            '/',
            [\maquinas_recreativas\Interfaces\Http\Controllers\HealthController::class, 'check'],
            []
        );
        error_log("Ruta registrada: GET /");
        
        // Intentar cargar rutas desde archivos
        $this->loadRouteFiles();
        
        error_log("Total rutas registradas: " . count($this->router->getRoutes()));
    }

    private function loadRouteFiles(): void
    {
        $routesPath = __DIR__ . '/../interfaces/http/routes/';
        
        if (!is_dir($routesPath)) {
            error_log("El directorio de rutas no existe: " . $routesPath);
            return;
        }
        
        $routeFiles = [
            'index.php',
            'auth.routes.php',
            'usuario.public.routes.php',
            'usuario.private.routes.php',
            'administrador.routes.php',
            'comercio.routes.php',
            'maquina.routes.php',
            'componente.routes.php',
            'distribucion.routes.php',
            'contabilidad.routes.php',
            'historial.routes.php',
            'notificacion.routes.php',
            'reporte.routes.php',
            'comentario.routes.php'
        ];
        
        foreach ($routeFiles as $file) {
            $filePath = $routesPath . $file;
            if (file_exists($filePath)) {
                error_log("Cargando archivo de rutas: " . $filePath);
                $routes = require $filePath;
                if (is_array($routes)) {
                    foreach ($routes as $route) {
                        $this->router->add(
                            $route['method'],
                            $route['path'],
                            $route['handler'],
                            $route['middleware'] ?? []
                        );
                        error_log("  Ruta cargada: " . $route['method'] . " " . $route['path']);
                    }
                }
            } else {
                error_log("Archivo no encontrado: " . $filePath);
            }
        }
    }

    public function run(): void
    {
        try {
            if ($this->handleSpecialFiles()) {
                return;
            }
            
            $method = $this->request->getMethod();
            $path = $this->request->getPath();
            
            error_log("=== DEBUG ROUTE ===");
            error_log("Method: " . $method);
            error_log("Path: " . $path);
            error_log("Full URI: " . $this->request->getUri());
            error_log("Total routes in router: " . count($this->router->getRoutes()));
            
            $this->pipeline->handle($this->request, function ($request) use ($method, $path) {
                $route = $this->router->match($method, $path);
                
                if (!$route) {
                    error_log("No route found for path: " . $path);
                    $this->response->json(['success' => false, 'message' => 'Endpoint no encontrado: ' . $path], 404);
                    $this->response->send();
                    return;
                }
                
                error_log("Route found! Handler: " . print_r($route['handler'], true));
                
                $routePipeline = new MiddlewarePipeline();
                foreach ($route['middleware'] as $middleware) {
                    $routePipeline->add($middleware, $middleware);
                }
                
                $routePipeline->handle($request, function ($request) use ($route) {
                    [$controllerClass, $method] = $route['handler'];
                    
                    error_log("Instanciando controlador: " . $controllerClass);
                    $controller = $this->resolveController($controllerClass);
                    
                    if (!$controller) {
                        error_log("Controlador no encontrado: " . $controllerClass);
                        throw new \RuntimeException("Controlador no encontrado: " . $controllerClass);
                    }
                    
                    if (!method_exists($controller, $method)) {
                        error_log("Método no encontrado: " . $controllerClass . "::" . $method);
                        throw new \RuntimeException("Método no encontrado: " . $controllerClass . "::" . $method);
                    }

                    $params = $route['params'] ?? [];
                    error_log("Ejecutando: " . $controllerClass . "::" . $method);
                    $result = $controller->$method($request, ...$params);

                    if ($result instanceof Response) {
                        $result->send();
                    } else {
                        $this->response->json($result);
                        $this->response->send();
                    }
                });
            });
        } catch (\Throwable $e) {
            $this->handleException($e);
        }
    }

    private function handleSpecialFiles(): bool
    {
        $path = $this->request->getPath();
        
        switch ($path) {
            case '/robots.txt':
                header("Content-Type: text/plain; charset=utf-8");
                echo "User-agent: *\nDisallow: /";
                return true;
                
            case '/sitemap.xml':
                header("Content-Type: application/xml; charset=utf-8");
                echo '<?xml version="1.0" encoding="UTF-8"?>';
                echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>';
                return true;
                
            case '/favicon.ico':
                http_response_code(204);
                return true;
        }
        
        $blockedRoutes = [
            '/latest/meta-data', '/computeMetadata', '/metadata',
            '/opc', '/openstack', '/actuator'
        ];
        
        foreach ($blockedRoutes as $blocked) {
            if (strpos($path, $blocked) === 0) {
                http_response_code(404);
                return true;
            }
        }
        
        return false;
    }

    private function resolveController(string $class): ?object
    {
        // Intentar obtener del contenedor Dependencies
        try {
            if (class_exists('\\Dependencies')) {
                $instance = \Dependencies::get($class);
                if ($instance !== null) {
                    error_log("Controlador obtenido del contenedor: " . $class);
                    return $instance;
                }
            }
        } catch (\Exception $e) {
            error_log("Error obteniendo {$class} del contenedor: " . $e->getMessage());
        }
        
        // Intentar crear instancia manualmente
        if (class_exists($class)) {
            try {
                error_log("Creando instancia manual de: " . $class);
                $reflection = new \ReflectionClass($class);
                $constructor = $reflection->getConstructor();
                
                if (!$constructor) {
                    return new $class();
                }
                
                $params = $constructor->getParameters();
                $args = [];
                
                foreach ($params as $param) {
                    $paramType = $param->getType();
                    
                    if ($paramType && method_exists($paramType, 'isBuiltin') && !$paramType->isBuiltin()) {
                        $paramClass = $paramType->getName();
                        try {
                            $args[] = $this->resolveController($paramClass);
                        } catch (\Exception $e) {
                            if ($param->isDefaultValueAvailable()) {
                                $args[] = $param->getDefaultValue();
                            } else {
                                $args[] = null;
                            }
                        }
                    } elseif ($param->isDefaultValueAvailable()) {
                        $args[] = $param->getDefaultValue();
                    } else {
                        $args[] = null;
                    }
                }
                
                return $reflection->newInstanceArgs($args);
            } catch (\ArgumentCountError $e) {
                error_log("No se puede instanciar {$class}: " . $e->getMessage());
                return null;
            }
        }
        
        error_log("Clase no encontrada: " . $class);
        return null;
    }

    private function handleException(\Throwable $e): void
    {
        error_log("App Error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
        error_log("Trace: " . $e->getTraceAsString());
        
        $response = [
            'success' => false,
            'message' => $this->config['debug'] ? $e->getMessage() : 'Error interno del servidor'
        ];
        
        if ($this->config['debug']) {
            $response['trace'] = $e->getTraceAsString();
            $response['file'] = $e->getFile();
            $response['line'] = $e->getLine();
        }
        
        if (ob_get_level() > 0) {
            ob_clean();
        }
        
        $this->response->json($response, 500);
        $this->response->send();
    }
}