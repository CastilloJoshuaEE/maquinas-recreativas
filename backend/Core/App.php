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

    private string $apiBasePath;

    private function __construct()
    {
        $this->request = new Request();
        $this->response = new Response();
        $this->router = new Router();
        $this->pipeline = new MiddlewarePipeline();
        $this->loadConfig();

        $this->apiBasePath = defined('API_BASE_PATH') ? API_BASE_PATH : '/api/public';

        $this->handleCors();

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

    /**
     * Maneja CORS al inicio del ciclo de vida de la peticion.
     * Usa CORS_ALLOWED_ORIGINS definida en backend/bootstrap/cors.php.
     * No define su propia lista de origenes.
     */
    private function handleCors(): void
    {
        if (defined('TEST_ENVIRONMENT') && TEST_ENVIRONMENT === true) {
            return;
        }

        $origin = rtrim($_SERVER['HTTP_ORIGIN'] ?? '', '/');
        $allowedOrigins = defined('CORS_ALLOWED_ORIGINS') ? CORS_ALLOWED_ORIGINS : [];

        $isAllowed = in_array($origin, $allowedOrigins, true);

        if (empty($origin) || $isAllowed) {
            header("Access-Control-Allow-Origin: " . ($origin ?: '*'));
            header("Access-Control-Allow-Credentials: true");
            header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS");
            header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept, Origin");
            header("Access-Control-Max-Age: 86400");
            header("Vary: Origin, Access-Control-Request-Method, Access-Control-Request-Headers");
        }

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit(0);
        }
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
        error_log("API Base Path: " . $this->apiBasePath);

        $this->router->add(
            'GET',
            $this->apiBasePath . '/health',
            [\maquinas_recreativas\Interfaces\Http\Controllers\HealthController::class, 'check'],
            []
        );
        error_log("Ruta registrada: GET " . $this->apiBasePath . "/health");

        $this->router->add(
            'GET',
            $this->apiBasePath . '/test-db',
            [\maquinas_recreativas\Interfaces\Http\Controllers\HealthController::class, 'testDb'],
            []
        );
        error_log("Ruta registrada: GET " . $this->apiBasePath . "/test-db");

        $this->router->add(
            'GET',
            '/',
            [\maquinas_recreativas\Interfaces\Http\Controllers\HealthController::class, 'check'],
            []
        );
        error_log("Ruta registrada: GET /");

        $this->router->add(
            'POST',
            $this->apiBasePath . '/usuario/login',
            [\maquinas_recreativas\Interfaces\Http\Controllers\UsuarioController::class, 'login'],
            []
        );
        error_log("Ruta registrada: POST " . $this->apiBasePath . "/usuario/login");

        $this->router->add(
            'POST',
            $this->apiBasePath . '/usuario/recuperar-usuario',
            [\maquinas_recreativas\Interfaces\Http\Controllers\UsuarioController::class, 'updateUsername'],
            []
        );
        error_log("Ruta registrada: POST " . $this->apiBasePath . "/usuario/recuperar-usuario");

        $this->router->add(
            'POST',
            $this->apiBasePath . '/usuario/logout',
            [\maquinas_recreativas\Interfaces\Http\Controllers\UsuarioController::class, 'logout'],
            []
        );
        error_log("Ruta registrada: POST " . $this->apiBasePath . "/usuario/logout");

        $this->router->add(
            'GET',
            $this->apiBasePath . '/usuario/perfil',
            [\maquinas_recreativas\Interfaces\Http\Controllers\UsuarioController::class, 'getProfile'],
            ['auth']
        );
        error_log("Ruta registrada: GET " . $this->apiBasePath . "/usuario/perfil");

        $this->router->add(
            'POST',
            $this->apiBasePath . '/usuario/register',
            [\maquinas_recreativas\Interfaces\Http\Controllers\UsuarioController::class, 'register'],
            []
        );
        error_log("Ruta registrada: POST " . $this->apiBasePath . "/usuario/register");

        $this->router->add(
            'POST',
            $this->apiBasePath . '/usuario/recuperar-contrasena',
            [\maquinas_recreativas\Interfaces\Http\Controllers\UsuarioController::class, 'resetPassword'],
            []
        );
        error_log("Ruta registrada: POST " . $this->apiBasePath . "/usuario/recuperar-contrasena");

        $this->router->add(
            'POST',
            $this->apiBasePath . '/usuario/buscar-email',
            [\maquinas_recreativas\Interfaces\Http\Controllers\UsuarioController::class, 'buscarPorEmail'],
            []
        );
        error_log("Ruta registrada: POST " . $this->apiBasePath . "/usuario/buscar-email");

        $this->loadRouteFiles();

        error_log("Total rutas registradas: " . count($this->router->getRoutes()));
    }

    private function loadRouteFiles(): void
    {
        $routesPath = __DIR__ . '/../Interfaces/Http/Routes/';

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
                        $path = $route['path'];
                        if (strpos($path, $this->apiBasePath) !== 0) {
                            $path = $this->apiBasePath . $path;
                        }
                        $this->router->add(
                            $route['method'],
                            $path,
                            $route['handler'],
                            $route['middleware'] ?? []
                        );
                        error_log("  Ruta cargada: " . $route['method'] . " " . $path);
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

            $response = $this->pipeline->handle($this->request, function ($request) use ($method, $path) {
                $route = $this->router->match($method, $path);

                if (!$route) {
                    error_log("No route found for path: " . $path);
                    return (new Response())->json(
                        ['success' => false, 'message' => 'Endpoint no encontrado: ' . $path],
                        404
                    );
                }

                $routePipeline = new MiddlewarePipeline();
                foreach ($route['middleware'] as $middleware) {
                    $routePipeline->add($middleware, $middleware);
                }

                return $routePipeline->handle($request, function ($request) use ($route) {
                    [$controllerClass, $method] = $route['handler'];

                    $controller = $this->resolveController($controllerClass);
                    if (!$controller) {
                        throw new \RuntimeException("Controlador no encontrado: " . $controllerClass);
                    }
                    if (!method_exists($controller, $method)) {
                        throw new \RuntimeException("Metodo no encontrado: " . $controllerClass . "::" . $method);
                    }

                    $params = $route['params'] ?? [];
                    $result = $controller->$method($request, ...$params);

                    return $result instanceof Response ? $result : (new Response())->json($result);
                });
            });

            if ($response instanceof Response && !$response->isSent()) {
                $response->send();
            }
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