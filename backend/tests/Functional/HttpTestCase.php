<?php
// tests/Functional/HttpTestCase.php

require_once __DIR__ . '/../../Config/constants.php';
require_once __DIR__ . '/../../Infrastructure/Security/CifradoHelper.php';

abstract class HttpTestCase {
    protected $baseUrl = 'http://localhost:8000/api/public';
    protected $cookies = [];
    protected $lastResponse;
    protected $lastHttpCode;
    protected $assertionCount = 0;
    protected $assertionFailures = [];
    protected $requestCount = 0;
    protected $maxRetries = 3;
    protected $sessionCookieFile;
    
    // Usuario administrador ya existente en TestDatabase
    protected $adminUser = [
        'usuario_asignado' => 'admin_test',
        'contrasena' => 'admin123'
    ];
    
    public function __construct() {
        $this->sessionCookieFile = sys_get_temp_dir() . '/phpunit_cookies_' . uniqid() . '.txt';
    }
    
    public function __destruct() {
        if (file_exists($this->sessionCookieFile)) {
            @unlink($this->sessionCookieFile);
        }
    }
    
    // Asegurar que el administrador existe (login exitoso)
    protected function ensureAdminExists(): bool {
        $response = $this->request('POST', '/usuario/login', $this->adminUser);
        if ($this->isSuccessResponse($response)) {
            return true;
        }
        // Si falla, crear administrador mediante registro normal y activación por BD (no lo usaremos)
        return false;
    }
    
    // Login como administrador
    protected function loginAsAdmin(): bool {
        if (!$this->ensureAdminExists()) return false;
        $response = $this->request('POST', '/usuario/login', $this->adminUser);
        return $this->isSuccessResponse($response);
    }
    
    // Registrar usuario mediante endpoint de administrador
    protected function registrarUsuarioAdmin(array $userData): ?array {
        $response = $this->request('POST', '/administrador/usuarios', $userData);
        if ($this->isSuccessResponse($response) && isset($response['id'])) {
            return $response;
        }
        return null;
    }
    
    // Buscar un usuario por email después de crearlo (para obtener usuario_asignado)
    protected function buscarUsuarioPorEmail(string $email): ?array {
        $response = $this->request('GET', '/administrador/usuarios');
        if (!$this->isSuccessResponse($response)) return null;
        $usuarios = $response['usuarios'] ?? [];
        foreach ($usuarios as $usuario) {
            if ($usuario['email'] === $email) {
                return $usuario;
            }
        }
        return null;
    }
    
    protected function request($method, $endpoint, $data = null, $headers = [], $retry = 0) {
        $this->requestCount++;
        echo "      -> Request #{$this->requestCount}: $method $endpoint\n";
        usleep(100000);
        
        $url = $this->baseUrl . $endpoint;
        $ch = curl_init($url);
        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HEADER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_COOKIEFILE => $this->sessionCookieFile,
            CURLOPT_COOKIEJAR => $this->sessionCookieFile,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json',
                'User-Agent: PHPUnit-Test'
            ]
        ];
        if (!empty($headers)) $options[CURLOPT_HTTPHEADER] = array_merge($options[CURLOPT_HTTPHEADER], $headers);
        if (in_array($method, ['POST','PUT','PATCH']) && $data !== null) {
            $options[CURLOPT_POSTFIELDS] = json_encode($data, JSON_UNESCAPED_UNICODE);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->lastResponse = ['success' => false, 'error' => 'JSON encode error'];
                return $this->lastResponse;
            }
        }
        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);
        if (curl_error($ch)) {
            $this->lastResponse = ['success' => false, 'error' => curl_error($ch)];
            curl_close($ch);
            return $this->lastResponse;
        }
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $this->lastHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $body = substr($response, $headerSize);
        $this->extractCookies(substr($response, 0, $headerSize));
        curl_close($ch);
        echo "      HTTP Status: {$this->lastHttpCode}\n";
        $this->lastResponse = json_decode($body, true) ?? ['success' => false, 'raw' => $body];
        if ($this->lastHttpCode === 429 && $retry < $this->maxRetries) {
            sleep(pow(2,$retry)*2);
            return $this->request($method,$endpoint,$data,$headers,$retry+1);
        }
        
        return $this->lastResponse;
    }
    
    protected function extractCookies($headerString) {
        preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $headerString, $matches);
        foreach ($matches[1] as $cookie) {
            $parts = explode('=', $cookie, 2);
            if (count($parts) == 2) $this->cookies[$parts[0]] = $parts[1];
        }
    }
    
    public function clearCookies(): void {
        $this->cookies = [];
        if (file_exists($this->sessionCookieFile)) @unlink($this->sessionCookieFile);
        $this->sessionCookieFile = sys_get_temp_dir() . '/phpunit_cookies_' . uniqid() . '.txt';
    }
    
    protected function isSuccessResponse($response): bool {
        if (!is_array($response)) return false;
        // Algunos endpoints devuelven directamente datos sin campo 'success'
        if (isset($response['success'])) return $response['success'] === true;
        // Si no hay 'success' pero hay 'id' o 'usuarios', asumimos éxito (código 2xx)
        if ($this->lastHttpCode >= 200 && $this->lastHttpCode < 300) return true;
        return false;
    }
    
    protected function assertResponseSuccess($message = '') {
        $this->assertionCount++;
        $success = $this->isSuccessResponse($this->lastResponse);
        if (!$success) {
            $error = ($message ? "$message: " : '') . json_encode($this->lastResponse, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            $this->assertionFailures[] = $error;
            echo "      FAIL: $error\n";
        } else {
            echo "      OK\n";
        }
        return $success;
    }
    
    protected function assertNotNull($value, $message = '') {
        $this->assertionCount++;
        if ($value === null || $value === '') {
            $this->assertionFailures[] = $message ?: 'Value should not be null or empty';
            echo "      FAIL: {$this->assertionFailures[count($this->assertionFailures)-1]}\n";
            return false;
        }
        return true;
    }
    
    protected function assertTrue($condition, $message = '') {
        $this->assertionCount++;
        if ($condition !== true) {
            $this->assertionFailures[] = $message ?: 'Condition must be true';
            echo "      FAIL: {$this->assertionFailures[count($this->assertionFailures)-1]}\n";
            return false;
        }
        return true;
    }
    
    protected function assertArrayHasKey($key, $array, $message = '') {
        $this->assertionCount++;
        if (!is_array($array) || !array_key_exists($key, $array)) {
            $this->assertionFailures[] = $message ?: "Array does not contain key '$key'";
            echo "      FAIL: {$this->assertionFailures[count($this->assertionFailures)-1]}\n";
            return false;
        }
        return true;
    }
    
    protected function assertNotEmpty($value, $message = '') {
        $this->assertionCount++;
        if (empty($value)) {
            $this->assertionFailures[] = $message ?: 'Value should not be empty';
            echo "      FAIL: {$this->assertionFailures[count($this->assertionFailures)-1]}\n";
            return false;
        }
        return true;
    }
    
    public function getAssertionSummary() {
        return [
            'total' => $this->assertionCount,
            'failures' => count($this->assertionFailures),
            'failures_list' => $this->assertionFailures
        ];
    }
}