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
        $this->registerRoutes();
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

    private function registerRoutes(): void
    {
        $routesPath = __DIR__ . '/../interfaces/http/routes/';
        
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
                $routes = require $filePath;
                if (is_array($routes)) {
                    foreach ($routes as $route) {
                        $this->router->add(
                            $route['method'],
                            $route['path'],
                            $route['handler'],
                            $route['middleware'] ?? []
                        );
                    }
                }
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
            
            // Log para depuración
            error_log("=== DEBUG ROUTE ===");
            error_log("Method: " . $method);
            error_log("Path: " . $path);
            error_log("Full URI: " . $this->request->getUri());
            
            $this->pipeline->handle($this->request, function ($request) use ($method, $path) {
                $route = $this->router->match($method, $path);
                
                error_log("Route matched: " . ($route ? 'YES' : 'NO'));
                if ($route) {
                    error_log("Route handler: " . print_r($route['handler'], true));
                }
                
                if (!$route) {
                    error_log("No route found for path: " . $path);
                    $this->response->json(['success' => false, 'message' => 'Endpoint no encontrado: ' . $path], 404);
                    $this->response->send();
                    return;
                }
                
                $routePipeline = new MiddlewarePipeline();
                foreach ($route['middleware'] as $middleware) {
                    $routePipeline->add($middleware, $middleware);
                }
                
                $routePipeline->handle($request, function ($request) use ($route) {
                    [$controllerClass, $method] = $route['handler'];
                    $controller = $this->resolveController($controllerClass);
                    
                    if (!$controller || !method_exists($controller, $method)) {
                        throw new \RuntimeException("Handler inválido para la ruta");
                    }

                    $params = $route['params'] ?? [];
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

    /**
     * Resuelve un controlador desde el contenedor
     */
    private function resolveController(string $class): ?object
    {
        try {
            if (class_exists('\\Dependencies')) {
                $instance = \Dependencies::get($class);
                if ($instance !== null) {
                    return $instance;
                }
            }
        } catch (\Exception $e) {
            error_log("Error obteniendo {$class} del contenedor: " . $e->getMessage());
        }
        
        if (class_exists($class)) {
            try {
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