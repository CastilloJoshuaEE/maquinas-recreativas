<?php
// tests/Functional/HttpTestCase.php

require_once __DIR__ . '/../../config/database.php';
abstract class HttpTestCase {
    protected $baseUrl = 'http://localhost:8000';
    protected $cookies = [];
    protected $lastResponse;
    protected $lastHttpCode;
    protected $assertionCount = 0;
    protected $assertionFailures = [];
    protected $requestCount = 0;
    protected $maxRetries = 3;
    
    /**
     * Realiza una petición HTTP con manejo de rate limiting y reintentos
     */
    // tests/Functional/HttpTestCase.php

protected function request($method, $endpoint, $data = null, $headers = [], $retry = 0) {
    $this->requestCount++;
    $requestId = $this->requestCount;
    
    echo "      → Request #{$requestId}: $method $endpoint\n";
    
    // Espera base según el tipo de endpoint
    $baseWait = 300000; // 0.3 segundos
    if (strpos($endpoint, '/usuario/login') !== false || 
        strpos($endpoint, '/usuario/register') !== false) {
        $baseWait = 800000; // 0.8 segundos para login/register
    }
    
    // Espera progresiva según el número de requests
    $progressiveWait = $baseWait * (1 + floor($this->requestCount / 10));
    usleep($progressiveWait);
    
    $url = $this->baseUrl . $endpoint;
    $ch = curl_init($url);
    
    $options = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HEADER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_FOLLOWLOCATION => true
    ];
    
    // IMPORTANTE: Habilitar cookies de sesión
    $cookieFile = sys_get_temp_dir() . '/curl_cookies_' . uniqid() . '.txt';
    
    // Usar archivo de cookies para mantener sesión entre requests
    $options[CURLOPT_COOKIEFILE] = $cookieFile;
    $options[CURLOPT_COOKIEJAR] = $cookieFile;
    
    // Cookies manuales como respaldo
    if (!empty($this->cookies)) {
        $cookieString = '';
        foreach ($this->cookies as $name => $value) {
            $cookieString .= "$name=$value; ";
        }
        $options[CURLOPT_COOKIE] = $cookieString;
    }
    
    // Headers
    $httpHeaders = [
        'Content-Type: application/json',
        'User-Agent: PHPUnit' 
    ];

    if (!empty($headers)) {
        $httpHeaders = array_merge($httpHeaders, $headers);
    }

    $options[CURLOPT_HTTPHEADER] = $httpHeaders;
    
    // Datos
    if (in_array($method, ['POST', 'PUT', 'PATCH']) && $data) {
        $options[CURLOPT_POSTFIELDS] = json_encode($data, JSON_UNESCAPED_UNICODE);
    }
    
    curl_setopt_array($ch, $options);
    
    $response = curl_exec($ch);
    
    if (curl_error($ch)) {
        $this->lastResponse = ['error' => curl_error($ch)];
        curl_close($ch);
        @unlink($cookieFile);
        return $this->lastResponse;
    }
    
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $this->lastHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    $headers = substr($response, 0, $headerSize);
    $this->extractCookies($headers);
    
    $body = substr($response, $headerSize);
    $this->lastResponse = json_decode($body, true);
    
    curl_close($ch);
    @unlink($cookieFile);
    
    // Si hay rate limiting (429), esperar más y reintentar
    if ($this->lastHttpCode === 429 && $retry < $this->maxRetries) {
        $waitTime = pow(2, $retry) * 5; // 5, 10, 20 segundos
        echo "      ⚠️  Rate limit detected! Esperando {$waitTime} segundos (intento " . ($retry+1) . "/{$this->maxRetries})...\n";
        sleep($waitTime);
        return $this->request($method, $endpoint, $data, $headers, $retry + 1);
    }
    
    return $this->lastResponse;
}
    
    /**
     * Extrae cookies de la respuesta
     */
    private function extractCookies($headerString) {
        preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $headerString, $matches);
        foreach ($matches[1] as $cookie) {
            $parts = explode('=', $cookie, 2);
            if (count($parts) == 2) {
                $this->cookies[$parts[0]] = $parts[1];
            }
        }
    }
    
    /**
     * Limpia las cookies (útil entre pruebas)
     */
    public function clearCookies(): void {
        $this->cookies = [];
    }
    
    /**
     * ASSERT: Respuesta exitosa
     */
    protected function assertResponseSuccess($message = '') {
        $this->assertionCount++;
        $success = isset($this->lastResponse['success']) && $this->lastResponse['success'] === true;
        
        if (!$success) {
            $error = $message ? "$message: " : '';
            $error .= json_encode($this->lastResponse, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            $this->assertionFailures[] = $error;
            echo "      ❌ $error\n";
        } else {
            echo "      ✅ OK\n";
        }
        
        return $success;
    }
    
    protected function assertNotNull($value, $message = '') {
        $this->assertionCount++;
        $success = $value !== null;
        
        if (!$success) {
            $this->assertionFailures[] = $message ?: 'El valor no debe ser nulo';
            echo "      ❌ {$this->assertionFailures[count($this->assertionFailures)-1]}\n";
        }
        
        return $success;
    }
    
    protected function assertArrayHasKey($key, $array, $message = '') {
        $this->assertionCount++;
        $success = is_array($array) && array_key_exists($key, $array);
        
        if (!$success) {
            $this->assertionFailures[] = $message ?: "El array no contiene la clave '$key'";
            echo "      ❌ {$this->assertionFailures[count($this->assertionFailures)-1]}\n";
        }
        
        return $success;
    }
    
    protected function assertEquals($expected, $actual, $message = '') {
        $this->assertionCount++;
        $success = $expected == $actual;
        
        if (!$success) {
            $error = $message ?: "Valores no coinciden";
            $error .= " - Esperado: $expected, Actual: $actual";
            $this->assertionFailures[] = $error;
            echo "      ❌ $error\n";
        }
        
        return $success;
    }
    
    protected function assertTrue($condition, $message = '') {
        $this->assertionCount++;
        $success = $condition === true;
        
        if (!$success) {
            $this->assertionFailures[] = $message ?: 'La condición debe ser verdadera';
            echo "      ❌ {$this->assertionFailures[count($this->assertionFailures)-1]}\n";
        }
        
        return $success;
    }
    
    protected function assertHttpCode($expectedCode) {
        $this->assertionCount++;
        $condition = ($this->lastHttpCode == $expectedCode);
        
        if (!$condition) {
            $errorMsg = "Código HTTP esperado $expectedCode, recibido {$this->lastHttpCode}";
            $this->assertionFailures[] = $errorMsg;
            echo "      ❌ $errorMsg\n";
        }
        
        return $condition;
    }
    
    public function getAssertionSummary() {
        return [
            'total' => $this->assertionCount,
            'failures' => count($this->assertionFailures),
            'failures_list' => $this->assertionFailures
        ];
    }
    
    public function __construct() {
        // Constructor vacío
    }
}