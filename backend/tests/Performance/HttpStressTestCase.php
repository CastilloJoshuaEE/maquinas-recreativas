<?php
// tests/Performance/HttpStressTestCase.php

class HttpStressTestCase {
    protected $baseUrl = 'http://localhost:8000';
    protected $cookies = [];
    protected $lastResponse;
    protected $lastHttpCode;
    protected $requestCount = 0;
    protected $maxRetries = 2;
    
    /**
     * Realiza una petición HTTP con manejo de reintentos
     */
    protected function request($method, $endpoint, $data = null, $headers = [], $retry = 0) {
        $this->requestCount++;
        
        $url = $this->baseUrl . $endpoint;
        $ch = curl_init($url);
        
        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HEADER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
        ];
        
        // Cookies
        if (!empty($this->cookies)) {
            $cookieString = '';
            foreach ($this->cookies as $name => $value) {
                $cookieString .= "$name=$value; ";
            }
            $options[CURLOPT_COOKIE] = rtrim($cookieString, '; ');
        }
        
        // Headers
        $httpHeaders = ['Content-Type: application/json'];
        if (!empty($headers) && is_array($headers)) {
            $httpHeaders = array_merge($httpHeaders, $headers);
        }
        $options[CURLOPT_HTTPHEADER] = $httpHeaders;
        
        // Datos
        if (in_array($method, ['POST', 'PUT', 'PATCH']) && $data !== null) {
            $options[CURLOPT_POSTFIELDS] = json_encode($data);
        }
        
        curl_setopt_array($ch, $options);
        
        $response = curl_exec($ch);
        
        if (curl_error($ch)) {
            $this->lastResponse = ['error' => curl_error($ch)];
            curl_close($ch);
            return $this->lastResponse;
        }
        
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $this->lastHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        $headersStr = substr($response, 0, $headerSize);
        $this->extractCookies($headersStr);
        
        $body = substr($response, $headerSize);
        $decoded = json_decode($body, true);
        $this->lastResponse = $decoded !== null ? $decoded : ['raw_body' => $body];
        
        curl_close($ch);
        
        // Reintentar en caso de rate limiting
        if ($this->lastHttpCode === 429 && $retry < $this->maxRetries) {
            $waitTime = pow(2, $retry);
            echo "        Rate limit (429) - Reintentando en {$waitTime}s (intento " . ($retry + 1) . "/{$this->maxRetries})\n";
            sleep($waitTime);
            return $this->request($method, $endpoint, $data, $headers, $retry + 1);
        }
        
        return $this->lastResponse;
    }
    
    /**
     * Extrae cookies de la respuesta
     */
    protected function extractCookies($headerString) {
        preg_match_all('/^Set-Cookie:\s*([^;]+)/mi', $headerString, $matches);
        foreach ($matches[1] as $cookie) {
            $parts = explode('=', $cookie, 2);
            if (count($parts) == 2) {
                $this->cookies[$parts[0]] = $parts[1];
            }
        }
    }
    
    /**
     * Limpia las cookies
     */
    public function clearCookies() {
        $this->cookies = [];
    }
    
    /**
     * Verifica que la respuesta fue exitosa
     */
    protected function assertResponseSuccess($message = '') {
        $success = isset($this->lastResponse['success']) && $this->lastResponse['success'] === true;
        
        if (!$success && !empty($message)) {
            echo "        {$message}\n";
            if ($this->lastResponse) {
                echo "      Respuesta: " . json_encode($this->lastResponse) . "\n";
            }
        }
        
        return $success;
    }
    
    /**
     * Verifica que un valor no sea nulo
     */
    protected function assertNotNull($value, $message = '') {
        if ($value === null && !empty($message)) {
            echo "        {$message}\n";
        }
        return $value !== null;
    }
    
    /**
     * Verifica que un array tenga una clave
     */
    protected function assertArrayHasKey($key, $array, $message = '') {
        $hasKey = is_array($array) && array_key_exists($key, $array);
        if (!$hasKey && !empty($message)) {
            echo "        {$message}\n";
        }
        return $hasKey;
    }
    
    /**
     * Verifica que una condición sea verdadera
     */
    protected function assertTrue($condition, $message = '') {
        if (!$condition && !empty($message)) {
            echo "        {$message}\n";
        }
        return $condition;
    }
    
    public function __construct() {
        // Constructor vacío
    }
}