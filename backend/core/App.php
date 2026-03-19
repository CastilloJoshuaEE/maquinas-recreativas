<?php
/**
 * RecreaSys - Core Application
 * 
 * Clase principal que orquesta toda la aplicación.
 * Implementa el patrón Singleton.
 * 
 * @package RecreaSys\Core
 * @author Tu Equipo
 * @version 1.0
 */
namespace RecreaSys\Core;
use RangeException;
use RecreaSys\Core\Router;
use RecreaSys\Core\Request;
use RecreaSys\Core\Response;
use RecreaSys\Core\MiddlewarePipeline;
class App{
    private static ?self $instance = null;
    private Router $router;
    private Request $request;
    private Response $response;
    private MiddlewarePipeline $pipeline;
    private array $config;
    /**
     * Constructor privado (Singleton)
     */
    private function __construct(){
        $this->request= new Request();
        $this->response= new Response();
        $this->router = new Router();
        $this->pipeline=new MiddlewarePipeline();
        $this->loadConfig();
        $this->registerMiddleware();
        $this->registerRoutes();
    }
    /**
     * Obtiene la instancia única de la aplicación
     * 
     * @return self
     */
    public static function getInstance():self{
        if(self::$instance === null){
            self::$instance=new self();
        }
        return self::$instance;
    }     
    /**
     * Carga la configuración
     */
    private function loadConfig():void{
        $this->config=[
            'env'=>APP_ENV ??'production',
            'debug'=>(APP_ENV ??'production')==='development',
            'timezone'=>date_default_timezone_get()
        ];
    }
    /**
     * Registra los middlewares globales
     */
    private function registerMiddleware():void{
      // Middlewares en orden de ejecución
        $this->pipeline->add('cors', \RecreaSys\Middleware\CorsMiddleware::class);
        $this->pipeline->add('rate-limit', \RecreaSys\Middleware\RateLimitMiddleware::class);
        $this->pipeline->add('auth', \RecreaSys\Middleware\AuthMiddleware::class);
        $this->pipeline->add('json-response', \RecreaSys\Middleware\JsonResponseMiddleware::class);        
    }  
    /**
     * Registra las rutas de la aplicación
     */
    private function registerRoutes():void{
        // Cargar archivos de rutas
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
        foreach($routeFiles as $file){
            $filePath = $routesPath . $file;
            if(file_exists($filePath)){
                $routes= require $filePath;
                if(is_array($routes)){
                    foreach($routes as $route){
                        $this->router->add(
                            $route['method'],
                            $route['path'],
                            $route['handler'],
                            $route['middleware']??[]
                        );
                    }
                }
            }
        }        
    }
    
    /**
     * Ejecuta la aplicación
     */
    public function run():void{
        try{
            // Manejar archivos especiales primero
            if($this->handleSpecialFiles()){
                return;
            }
            // Manejar endpoint de reset rate limits
            if($this->request->getPath()==='/reset-rate-limits'){
                \applyRateLimitReset();
                return;
            }
            // Ejecutar pipeline de middlewares
            $this->pipeline->handle($this->request, function($request){
                // Buscar ruta
                $route = $this->router->match($request->getMethod(), $request->getPath());
                if(!$route){
                    $this->response->json(['success'=>false,'message'=> 'Enpoint no encontrado'], 404);
                    return;
                }
                // Ejecutar middleware específicos de la ruta
                $routePipeline= new MiddlewarePipeline();
                foreach($route['middleware']as $middleware){
                    $routePipeline->add($middleware, $middleware);
                }
                $routePipeline->handle($request, function($request) use($route){
                    [$controllerClass, $method] = $route['handler'];
                    $controller = $this->resolveController($controllerClass);
                    if(!$controller || !method_exists($controller, $method)){
                        throw new \RuntimeException("Handler inválido para la ruta");
                    }
                    // Invocar controlador
                    $result = $controller->$method($request);
                    //Enviar respuesta
                    if($result instanceof Response){
                        $result->send();
                    }else{
                        $this->response->json($result);
                    }



                });
            });
        }catch(\Throwable $e){
            $this->handleException($e);
        }
    }
    /**
     * Maneja archivos especiales (robots.txt, sitemap.xml, favicon.ico)
     * 
     * @return bool True si se manejó, false si no
     */ 
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
        
        // Bloquear rutas de scanners
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
     * 
     * @param string $class
     * @return object|null
     */
    private function resolveController(string $class): ?object
    {
        global $container;
        
        if (isset($container[$class])) {
            return $container[$class];
        }
        
        // Intentar crear instancia (fallback)
        if (class_exists($class)) {
            return new $class();
        }
        
        return null;
    }
    
    /**
     * Maneja excepciones no capturadas
     * 
     * @param \Throwable $e
     */
    private function handleException(\Throwable $e): void
    {
        error_log("App Error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
        
        $response = [
            'success' => false,
            'message' => $this->config['debug'] ? $e->getMessage() : 'Error interno del servidor'
        ];
        
        if ($this->config['debug']) {
            $response['trace'] = $e->getTraceAsString();
        }
        
        $this->response->json($response, 500);
    }
             
}