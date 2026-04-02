<?php
// tests/Functional/HttpTestCase.php

require_once __DIR__ . '/../../Config/constants.php';
require_once __DIR__ . '/../../Infrastructure/Security/CifradoHelper.php';

abstract class HttpTestCase {
    protected $baseUrl = 'http://localhost:8000';
    protected $cookies = [];
    protected $lastResponse;
    protected $lastHttpCode;
    protected $assertionCount = 0;
    protected $assertionFailures = [];
    protected $requestCount = 0;
    protected $maxRetries = 3;
    protected $sessionCookieFile;
    
    public function __construct() {
        $this->sessionCookieFile = sys_get_temp_dir() . '/phpunit_cookies_' . uniqid() . '.txt';
    }
    
    public function __destruct() {
        if (file_exists($this->sessionCookieFile)) {
            @unlink($this->sessionCookieFile);
        }
    }
    
    protected function request($method, $endpoint, $data = null, $headers = [], $retry = 0) {
        $this->requestCount++;
        
        echo "      -> Request #{$this->requestCount}: $method $endpoint\n";
        
        // Espera base entre requests para evitar rate limiting
        usleep(100000);
        
        $url = $this->baseUrl . $endpoint;
        $ch = curl_init($url);
        
        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HEADER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            // Usar el mismo archivo de cookies para todas las requests
            CURLOPT_COOKIEFILE => $this->sessionCookieFile,
            CURLOPT_COOKIEJAR => $this->sessionCookieFile,
        ];
        
        $httpHeaders = [
            'Content-Type: application/json',
            'Accept: application/json',
            'User-Agent: PHPUnit-Test'
        ];
        
        if (!empty($headers)) {
            $httpHeaders = array_merge($httpHeaders, $headers);
        }
        
        $options[CURLOPT_HTTPHEADER] = $httpHeaders;
        
        if (in_array($method, ['POST', 'PUT', 'PATCH']) && $data !== null) {
            $options[CURLOPT_POSTFIELDS] = json_encode($data, JSON_UNESCAPED_UNICODE);
            if (json_last_error() !== JSON_ERROR_NONE) {
                echo "      JSON encode error: " . json_last_error_msg() . "\n";
                $this->lastResponse = ['success' => false, 'error' => 'JSON encode error'];
                return $this->lastResponse;
            }
        }
        
        curl_setopt_array($ch, $options);
        
        $response = curl_exec($ch);
        
        if (curl_error($ch)) {
            $error = curl_error($ch);
            $this->lastResponse = ['success' => false, 'error' => $error];
            curl_close($ch);
            echo "      cURL Error: $error\n";
            return $this->lastResponse;
        }
        
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $this->lastHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        $headers = substr($response, 0, $headerSize);
        $body = substr($response, $headerSize);
        
        $this->extractCookies($headers);
        
        curl_close($ch);
        
        echo "      HTTP Status: {$this->lastHttpCode}\n";
        
        if ($this->lastHttpCode === 0) {
            $this->lastResponse = ['success' => false, 'error' => 'No response from server'];
            echo "      Error: No response from server\n";
            return $this->lastResponse;
        }
        
        $this->lastResponse = json_decode($body, true);
        
        if ($this->lastResponse === null && !empty($body)) {
            // Intentar limpiar BOM o caracteres no visibles
            $cleanBody = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $body);
            $this->lastResponse = json_decode($cleanBody, true);
            
            if ($this->lastResponse === null) {
                echo "      Raw response (first 200 chars): " . substr($body, 0, 200) . "\n";
                $this->lastResponse = ['success' => false, 'raw_response' => $body];
            }
        }
        
        // Si el cuerpo está vacío y el código es 200, puede ser un error interno
        if ($this->lastHttpCode === 200 && empty($body)) {
            echo "      WARNING: Empty response body with status 200\n";
            $this->lastResponse = ['success' => false, 'error' => 'Empty response body'];
        }
        
        // Rate limiting
        if ($this->lastHttpCode === 429 && $retry < $this->maxRetries) {
            $waitTime = pow(2, $retry) * 2;
            echo "      Rate limit detected. Waiting {$waitTime} seconds (attempt " . ($retry+1) . "/{$this->maxRetries})...\n";
            sleep($waitTime);
            return $this->request($method, $endpoint, $data, $headers, $retry + 1);
        }
        
        return $this->lastResponse;
    }
    private function extractCookies($headerString) {
        preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $headerString, $matches);
        foreach ($matches[1] as $cookie) {
            $parts = explode('=', $cookie, 2);
            if (count($parts) == 2) {
                $this->cookies[$parts[0]] = $parts[1];
            }
        }
    }
    
    public function clearCookies(): void {
        $this->cookies = [];
        if (file_exists($this->sessionCookieFile)) {
            @unlink($this->sessionCookieFile);
        }
        $this->sessionCookieFile = sys_get_temp_dir() . '/phpunit_cookies_' . uniqid() . '.txt';
    }
    
    protected function assertResponseSuccess($message = '') {
        $this->assertionCount++;
        
        if ($this->lastResponse === null) {
            $error = $message ? "$message: " : '';
            $error .= 'Response is null';
            $this->assertionFailures[] = $error;
            echo "      FAIL: $error\n";
            return false;
        }
        
        $success = isset($this->lastResponse['success']) && $this->lastResponse['success'] === true;
        
        if (!$success) {
            $error = $message ? "$message: " : '';
            $error .= json_encode($this->lastResponse, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            $this->assertionFailures[] = $error;
            echo "      FAIL: $error\n";
        } else {
            echo "      OK\n";
        }
        
        return $success;
    }
    
    protected function assertNotNull($value, $message = '') {
        $this->assertionCount++;
        $success = $value !== null && $value !== '';
        
        if (!$success) {
            $this->assertionFailures[] = $message ?: 'Value should not be null or empty';
            echo "      FAIL: {$this->assertionFailures[count($this->assertionFailures)-1]}\n";
        }
        
        return $success;
    }
    
    protected function assertArrayHasKey($key, $array, $message = '') {
        $this->assertionCount++;
        $success = is_array($array) && array_key_exists($key, $array);
        
        if (!$success) {
            $this->assertionFailures[] = $message ?: "Array does not contain key '$key'";
            echo "      FAIL: {$this->assertionFailures[count($this->assertionFailures)-1]}\n";
        }
        
        return $success;
    }
    
    protected function assertEquals($expected, $actual, $message = '') {
        $this->assertionCount++;
        $success = $expected == $actual;
        
        if (!$success) {
            $error = $message ?: "Values do not match";
            $error .= " - Expected: $expected, Actual: " . (is_scalar($actual) ? $actual : json_encode($actual));
            $this->assertionFailures[] = $error;
            echo "      FAIL: $error\n";
        }
        
        return $success;
    }
    
    protected function assertTrue($condition, $message = '') {
        $this->assertionCount++;
        $success = $condition === true;
        
        if (!$success) {
            $this->assertionFailures[] = $message ?: 'Condition must be true';
            echo "      FAIL: {$this->assertionFailures[count($this->assertionFailures)-1]}\n";
        }
        
        return $success;
    }
    
    protected function assertHttpCode($expectedCode) {
        $this->assertionCount++;
        $condition = ($this->lastHttpCode == $expectedCode);
        
        if (!$condition) {
            $errorMsg = "Expected HTTP code $expectedCode, got {$this->lastHttpCode}";
            $this->assertionFailures[] = $errorMsg;
            echo "      FAIL: $errorMsg\n";
        }
        
        return $condition;
    }
    
    protected function assertNotEmpty($value, $message = '') {
        $this->assertionCount++;
        $success = !empty($value);
        
        if (!$success) {
            $this->assertionFailures[] = $message ?: 'Value should not be empty';
            echo "      FAIL: {$this->assertionFailures[count($this->assertionFailures)-1]}\n";
        }
        
        return $success;
    }
    
    public function getAssertionSummary() {
        return [
            'total' => $this->assertionCount,
            'failures' => count($this->assertionFailures),
            'failures_list' => $this->assertionFailures
        ];
    }
}