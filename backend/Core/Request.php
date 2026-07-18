<?php
/**
 * maquinas_recreativas - Core Request
 * 
 * Abstracción de la petición HTTP.
 * 
 * @package maquinas_recreativas\Core
 * @author Tu Equipo
 * @version 1.0
 */
namespace maquinas_recreativas\Core;

class Request
{
    public ?array $user = null;
    private array $server;
    private array $get;
    private array $post;
    private array $cookies;
    private array $files;
    private array $headers;
    private ?array $jsonData = null;
    
    public function __construct()
    {
        $this->server = $_SERVER;
        $this->get = $_GET;
        $this->post = $_POST;
        $this->cookies = $_COOKIE;
        $this->files = $_FILES;
        $this->headers = $this->extractHeaders();
    }
    
    public function getHeaders(): array
    {
        return $this->headers;
    }
    
    private function extractHeaders(): array
    {
        $headers = [];
        foreach ($this->server as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $name = str_replace('_', '-', substr($key, 5));
                $headers[$name] = $value;
            }
        }
        return $headers;
    }
    
    public function getMethod(): string
    {
        return $this->server['REQUEST_METHOD'] ?? 'GET';
    }
    
    public function getUri(): string
    {
        return $this->server['REQUEST_URI'] ?? '/';
    }
    
    public function getPath(): string
    {
        $uri = $this->getUri();
        $path = parse_url($uri, PHP_URL_PATH);
        
        // Log para depuración
        error_log("=== Request::getPath ===");
        error_log("Original URI: " . $uri);
        error_log("Parsed path: " . $path);
        
        // Eliminar base path si existe
        $basePath = '/api/public';
        if (strpos($path, $basePath) === 0) {
            $path = substr($path, strlen($basePath));
            error_log("After removing basePath: " . $path);
        }
        
        // Eliminar /index.php
        $path = str_replace('/index.php', '', $path);
        
        // Normalizar
        $path = rtrim($path, '/');
        
        // Si está vacío, usar /
        if (empty($path)) {
            $path = '/';
        }
        
        error_log("Final path: " . $path);
        return $path;
    }
    
    public function query(?string $key = null, $default = null)
    {
        if ($key === null) {
            return $this->get;
        }
        return $this->get[$key] ?? $default;
    }
    
    public function post(?string $key = null, $default = null)
    {
        if ($key === null) {
            return $this->post;
        }
        return $this->post[$key] ?? $default;
    }
    
    public function json(?string $key = null, $default = null)
    {
        if ($this->jsonData === null) {
            $content = file_get_contents('php://input');
            $this->jsonData = json_decode($content, true) ?? [];
        }
        if ($key === null) {
            return $this->jsonData;
        }
        return $this->jsonData[$key] ?? $default;
    }
    
    public function header(string $name, $default = null)
    {
        $name = strtoupper(str_replace('-', '_', $name));
        return $this->headers[$name] ?? $default;
    }
    
    public function getClientIp(): string
    {
        $ips = [
            'HTTP_X_FORWARDED_FOR',
            'HTTP_CLIENT_IP',
            'HTTP_X_REAL_IP',
            'REMOTE_ADDR'
        ];
        foreach ($ips as $header) {
            if (isset($this->server[$header])) {
                $ipList = explode(',', $this->server[$header]);
                $ip = trim($ipList[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        return '0.0.0.0';
    }
    
    public function isAjax(): bool
    {
        return $this->header('X-Requested-With') === 'XMLHttpRequest';
    }
    
    public function isLocal(): bool
    {
        $ip = $this->getClientIp();
        return in_array($ip, ['127.0.0.1', '::1', 'localhost', '::ffff:127.0.0.1']);
    }
    
    public function all(): array
    {
        return array_merge($this->get, $this->post, $this->json());
    }
    
    public function input(string $key, $default = null)
    {
        return $this->json($key, $this->post($key, $this->query($key, $default)));
    }
}