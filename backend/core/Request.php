<?php
/**
 * RecreaSys - Core Request
 * 
 * Abstracción de la petición HTTP.
 * 
 * @package RecreaSys\Core
 * @author Tu Equipo
 * @version 1.0
 */
namespace RecreaSys\Core;
class Request{
    private array $server;
    private array $get;
    private array $post;
    private array $cookies;
    private array $files;
    private array $headers;
    private ?array $jsonData=null;
    public function __construct(){
        $this->server = $_SERVER;
        $this->get = $_GET;
        $this->post = $_POST;
        $this->cookies = $_COOKIE;
        $this->files = $_FILES;
        $this->headers = $this->extractHeaders();
    }
   /**
     * Extrae headers del servidor
     * 
     * @return array
     */
    private function extractHeaders(): array{
        $headers = [];
        foreach($this->server as $key => $value){
            if(strpos($key,'HTTP_')===0){
                $name = str_replace('_', '-', substr($key,5));
                $headers[$name] = $value;
            }
        }
        return $headers;
    }
    /**
     * Obtiene el método HTTP
     * 
     * @return string
     */
    public function getMethod():string{
        return $this->server['REQUEST_METHOD']??'GET';
    }
    /**
     * Obtiene la URI de la petición
     * 
     * @return string
     */
    public function getUri():string{
        return $this->server['REQUEST_URI']??'/';
    }    
    /**
     * Obtiene el path de la URI (sin query string)
     * 
     * @return string
     */
    public function getPath():string{
        $uri = $this->getUri();
        $path = parse_url($uri, PHP_URL_PATH);
        // Eliminar base path si existe
        $basePath = '/api/public';
        if(strpos($path, $basePath)=== 0){
            $path = substr($path, strlen($basePath));
        }
        $path = str_replace('/index.php','', $path);
        $path = rtrim($path, '/');
        return $path ?:'/';
    }    
    /**
     * Obtiene parámetros GET
     * 
     * @param string|null $key
     * @param mixed $default
     * @return mixed
     */
    public function query(?string $key=null, $default=null){
        if($key === null){
            return $this->get;
        }
        return $this->get[$key] ?? $default;
    }    
    /**
     * Obtiene parámetros POST
     * 
     * @param string|null $key
     * @param mixed $default
     * @return mixed
     */
    public function post(?string $key=null, $default=null){
        if($key === null){
            return $this->post;
        }
        return $this->post[$key] ?? $default;
    }
    /**
     * Obtiene datos JSON del body
     * 
     * @param string|null $key
     * @param mixed $default
     * @return mixed
     */
    public function json(?string $key=null, $default=null){
        if($this->jsonData===null){
            $content = file_get_contents('php://input');
            $this->jsonData = json_decode($content, true)??[];
        }
        if($key===null){
            return $this->jsonData;
        }
        return $this->jsonData[$key] ?? $default;
    }
    /**
     * Obtiene un header
     * 
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function header(string $name, $default=null){
        $name = strtoupper(str_replace('-', '_', $name));
        return $this->headers[$name] ?? $default;
    }    
    /**
     * Obtiene la IP del cliente
     * 
     * @return string
     */
    public function getClientIp():string{
        $ips = [
            'HTTP_X_FORWARDED_FOR',
            'HTTP_CLIENT_IP',
            'HTTP_X_REAL_IP',
            'REMOTE_ADDR'
        ];
        foreach($ips as $header){
            if(isset($this->server[$header])){
                $ipList= explode(',', $this->server[$header]);
                $ip=trim($ipList[0]);
                if(filter_var($ip, FILTER_VALIDATE_IP)){
                    return $ip;
                }

            }
        }
        return '0.0.0.0';
    }    
    /**
     * Verifica si la petición es AJAX
     * 
     * @return bool
     */
    public function isAjax():bool{
        return $this->header('X-Requested-With') === 'XMLHttpRequest';
    }
    /**
     * Verifica si es petición local
     * 
     * @return bool
     */
    public function isLocal():bool{
        $ip = $this->getClientIp();
        return in_array($ip, ['127.0.0.1', '::1', 'localhost', '::ffff:127.0.0.1']);
    }
    /**
     * Obtiene todos los datos de la petición
     * 
     * @return array
     */
public function all(): array
    {
        return array_merge($this->get, $this->post, $this->json());
    }
    
    /**
     * Obtiene un valor específico
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function input(string $key, $default = null)
    {
        return $this->json($key, $this->post($key, $this->query($key, $default)));
    }        
}