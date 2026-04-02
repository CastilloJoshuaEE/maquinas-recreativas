<?php
/**
 * Security Test Script for API
 * Ejecutar desde la raíz del backend: php tests/Security/security-test.php
 */

// Configurar entorno de pruebas
define('TEST_ENVIRONMENT', true);

// Determinar la raíz del proyecto (backend/)
$projectRoot = dirname(__DIR__, 2);

// Cargar autoloader
$autoloadPath = $projectRoot . '/vendor/autoload.php';
if (!file_exists($autoloadPath)) {
    die("Error: No se encuentra vendor/autoload.php en: " . $autoloadPath . "\n");
}
require_once $autoloadPath;

// Cargar configuración
$bootstrapEnv = $projectRoot . '/Bootstrap/env.php';
if (!file_exists($bootstrapEnv)) {
    die("Error: No se encuentra Bootstrap/env.php en: " . $bootstrapEnv . "\n");
}
require_once $bootstrapEnv;

$configConstants = $projectRoot . '/config/constants.php';
if (!file_exists($configConstants)) {
    die("Error: No se encuentra config/constants.php en: " . $configConstants . "\n");
}

require_once $configConstants;

use maquinas_recreativas\Infrastructure\Database\Database;
use maquinas_recreativas\Infrastructure\Security\CifradoHelper;

class APISecurityTest {
    private $baseUrl = 'http://localhost:8000';
    private $testResults = [];
    private $db;
    private $lastHttpCode;
    
    // Payloads para pruebas
    private $sqlPayloads = [
        "' OR '1'='1",
        "'; DROP TABLE usuario; --",
        "' UNION SELECT * FROM usuario--",
        "' OR 1=1--",
        "admin'--",
        "1' AND SLEEP(5)--"
    ];
    
    private $xssPayloads = [
        "<script>alert('XSS')</script>",
        "<img src=x onerror=alert('XSS')>",
        "<svg onload=alert('XSS')>",
        "javascript:alert('XSS')"
    ];
    
    public function __construct() {
        try {
            $this->db = new Database();
            $this->log(" Conexión a base de datos establecida", 'SUCCESS');
        } catch (\Exception $e) {
            $this->log("  Error de conexión a BD: " . $e->getMessage(), 'WARNING');
        }
        $this->baseUrl = getenv('APP_URL') ?: 'http://localhost:8000';
        $this->log(" URL base: " . $this->baseUrl, 'INFO');
    }
    
    /**
     * Ejecutar todas las pruebas
     */
    public function runAllTests() {
        $this->log("\nINICIANDO AUDITORÍA DE SEGURIDAD", 'INFO');
        $this->log("====================================\n", 'INFO');
        
        // 1. Probar SQL Injection en endpoints
        $this->testSQLInjection();
        
        // 2. Probar XSS en endpoints
        $this->testXSS();
        
        // 3. Probar rate limiting
        $this->testRateLimiting();
        
        // 4. Probar validaciones de entrada
        $this->testInputValidations();
        
        // 5. Probar protección de sesiones
        $this->testSessionProtection();
        
        // 6. Probar endpoints públicos
        $this->testPublicEndpoints();
        
        // 7. Probar encriptación de datos
        $this->testDataEncryption();
        
        // 8. Probar headers de seguridad
        $this->testSecurityHeaders();
        
        // Generar reporte
        $this->generateReport();
    }
    
    /**
     * Probar SQL Injection
     */
    private function testSQLInjection() {
        $this->log("\n TEST: SQL Injection", 'TEST');
        
        $endpoints = [
            '/usuario/login' => 'POST',
            '/usuario/buscar-email' => 'POST',
            '/administrador/usuarios' => 'GET'
        ];
        
        foreach ($endpoints as $endpoint => $method) {
            foreach ($this->sqlPayloads as $payload) {
                $data = $method === 'POST' ? ['usuario_asignado' => $payload, 'contrasena' => 'test'] : [];
                $response = $this->makeRequest($method, $endpoint, $data);
                
                if (is_string($response) && $this->containsSQLError($response)) {
                    $this->log("  Posible SQL Injection en $endpoint con payload: $payload", 'CRITICAL');
                    $this->addResult('SQL Injection', 'CRITICAL', "Endpoint $endpoint vulnerable con payload: $payload");
                    break;
                }
            }
        }
    }
    
    /**
     * Probar XSS
     */
    private function testXSS() {
        $this->log("\n TEST: XSS (Cross-Site Scripting)", 'TEST');
        
        $endpoints = [
            '/reportes/crear' => ['descripcion' => 'test'],
            '/comentarios' => ['comentario' => 'test']
        ];
        
        foreach ($endpoints as $endpoint => $baseData) {
            foreach ($this->xssPayloads as $payload) {
                $data = array_merge($baseData, ['descripcion' => $payload, 'comentario' => $payload]);
                $response = $this->makeRequest('POST', $endpoint, $data);
                
                if (is_string($response) && strpos($response, $payload) !== false) {
                    $this->log("  Posible XSS en $endpoint - payload no sanitizado", 'HIGH');
                    $this->addResult('XSS', 'HIGH', "Endpoint $endpoint podría ser vulnerable a XSS");
                    break;
                }
            }
        }
    }
    
/**
 * Probar rate limiting
 */
private function testRateLimiting() {
    $this->log("\n TEST: Rate Limiting", 'TEST');
    
    $endpoint = '/usuario/login';
    $maxRequestsToTest = 10;
    $rateLimited = false;
    $blockedAtRequest = 0;
    
    // Usar una IP ficticia para pruebas (asegura que no se confunda con localhost)
    $testIp = '192.168.100.100';
    
    for ($i = 0; $i < $maxRequestsToTest; $i++) {
        $ch = curl_init($this->baseUrl . $endpoint);
        
        $data = json_encode([
            'usuario_asignado' => "testuser$i",
            'contrasena' => 'wrongpassword'
        ]);
        
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'X-Security-Test: true',
                'X-Forwarded-For: ' . $testIp  // Forzar una IP diferente
            ],
            CURLOPT_HEADER => true,
            CURLOPT_TIMEOUT => 10
        ]);
        
        $response = curl_exec($ch);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headers = substr($response, 0, $headerSize);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        curl_close($ch);
        
        $this->log("Request $i: HTTP Code = $httpCode", 'INFO');
        
        if ($httpCode === 429) {
            $rateLimited = true;
            $blockedAtRequest = $i;
            $this->log(" Rate limiting detectado (HTTP 429) después de $i requests", 'SUCCESS');
            break;
        }
        
        usleep(50000);
    }
    
    if (!$rateLimited) {
        $this->log("  No se detectó rate limiting después de $maxRequestsToTest requests", 'MEDIUM');
        $this->addResult('Rate Limiting', 'MEDIUM', "No se detectó rate limiting después de $maxRequestsToTest requests");
    } else {
        $this->log(" Rate limiting funcionando correctamente (bloqueado en request #$blockedAtRequest)", 'SUCCESS');
        $this->addResult('Rate Limiting', 'SUCCESS', "Rate limiting detectado después de $blockedAtRequest requests");
    }
}
    
    /**
     * Probar validaciones de entrada
     */
    private function testInputValidations() {
        $this->log("\n TEST: Validaciones de Entrada", 'TEST');
        
        $testCases = [
            '/usuario/register' => [
                ['email' => 'invalid-email', 'contrasena' => '123'],
                ['email' => '', 'contrasena' => ''],
                ['nombre' => str_repeat('A', 500)]
            ],
            '/comercio/register' => [
                ['nombre' => '', 'tipo' => 'invalid'],
                ['telefono' => 'abc123']
            ]
        ];
        
        foreach ($testCases as $endpoint => $cases) {
            foreach ($cases as $case) {
                $response = $this->makeRequest('POST', $endpoint, $case);
                
                if (isset($response['success']) && $response['success'] === true) {
                    $this->log("  Endpoint $endpoint aceptó datos inválidos", 'HIGH');
                    $this->addResult('Missing Validation', 'HIGH', "$endpoint aceptó datos inválidos");
                }
            }
        }
    }
    
    /**
     * Probar protección de sesiones
     */
    private function testSessionProtection() {
        $this->log("\n TEST: Protección de Sesiones", 'TEST');
        
        // Obtener cookies de sesión
        $ch = curl_init($this->baseUrl . '/health');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        
        // Verificar cookies
        preg_match_all('/^Set-Cookie:\s*(.*)$/mi', $response, $cookies);
        
        foreach ($cookies[1] as $cookieHeader) {
            if (stripos($cookieHeader, 'PHPSESSID') !== false) {
                if (stripos($cookieHeader, 'httponly') === false) {
                    $this->log(" Cookie de sesión sin HttpOnly", 'HIGH');
                    $this->addResult('Session Protection', 'HIGH', 'Cookie PHPSESSID sin HttpOnly');
                } else {
                    $this->log(" HttpOnly presente", 'SUCCESS');
                }
                if (stripos($cookieHeader, 'secure') === false) {
                    $this->log(" Cookie de sesión sin Secure flag (esperado en localhost)", 'INFO');
                } else {
                    $this->log(" Secure flag presente", 'SUCCESS');
                }
                if (stripos($cookieHeader, 'SameSite') === false) {
                    $this->log(" Cookie de sesión sin SameSite", 'MEDIUM');
                    $this->addResult('Session Protection', 'MEDIUM', 'Cookie PHPSESSID sin SameSite');
                }
            }
        }
    }
    
    /**
     * Probar headers de seguridad
     */
    private function testSecurityHeaders() {
        $this->log("\n TEST: Security Headers", 'TEST');
        
        $ch = curl_init($this->baseUrl . '/health');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        $response = curl_exec($ch);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headersRaw = substr($response, 0, $headerSize);
        curl_close($ch);
        
        // Parsear headers
        $headers = [];
        $lines = explode("\n", $headersRaw);
        foreach ($lines as $line) {
            if (strpos($line, ':') !== false) {
                list($key, $value) = explode(':', $line, 2);
                $headers[strtolower(trim($key))] = trim($value);
            }
        }
        
        $requiredHeaders = [
            'X-Frame-Options' => ['DENY', 'SAMEORIGIN'],
            'X-Content-Type-Options' => ['nosniff'],
            'X-XSS-Protection' => ['1; mode=block'],
            'Referrer-Policy' => ['strict-origin-when-cross-origin']
        ];
        
        foreach ($requiredHeaders as $header => $expected) {
            $key = strtolower($header);
            if (isset($headers[$key])) {
                $this->log(" Header $header presente: " . $headers[$key], 'SUCCESS');
            } else {
                $this->log("  Header $header no presente", 'MEDIUM');
                $this->addResult('Security Headers', 'MEDIUM', "Header $header no presente");
            }
        }
        
        // Verificar Content-Security-Policy
        if (isset($headers['content-security-policy'])) {
            $this->log(" Content-Security-Policy presente", 'SUCCESS');
        } else {
            $this->log("  Content-Security-Policy no presente", 'MEDIUM');
            $this->addResult('Security Headers', 'MEDIUM', 'Content-Security-Policy no presente');
        }
    }
    
    /**
     * Probar endpoints públicos
     */
    private function testPublicEndpoints() {
        $this->log("\n TEST: Endpoints Públicos", 'TEST');
        
        $publicEndpoints = [
            '/health',
            '/test-db',
            '/usuario/login',
            '/usuario/register',
            '/usuario/buscar-email'
        ];
        
        foreach ($publicEndpoints as $endpoint) {
            $response = $this->makeRequest('GET', $endpoint);
            $httpCode = $this->getLastHttpCode();
            
            if ($httpCode === 200 || $httpCode === 401 || $httpCode === 405) {
                $this->log(" Endpoint público $endpoint accesible (código $httpCode)", 'SUCCESS');
            } else {
                $this->log("  Endpoint público $endpoint retorna código $httpCode", 'WARNING');
            }
        }
    }
    
    /**
     * Probar encriptación de datos sensibles
     */
    private function testDataEncryption() {
        $this->log("\n TEST: Encriptación de Datos", 'TEST');
        
        $testEmail = 'test_' . time() . '@example.com';
        $testCi = '1234567890';
        
        try {
            if (defined('ENCRYPT_METHOD') && defined('SECRET_KEY') && defined('SECRET_IV')) {
                $emailEncrypted = CifradoHelper::encriptar($testEmail);
                $ciEncrypted = CifradoHelper::encriptar($testCi);
                
                $emailDecrypted = CifradoHelper::desencriptar($emailEncrypted);
                $ciDecrypted = CifradoHelper::desencriptar($ciEncrypted);
                
                if ($emailDecrypted === $testEmail && $ciDecrypted === $testCi) {
                    $this->log(" Encriptación/desencriptación funciona correctamente", 'SUCCESS');
                } else {
                    $this->log("  Problemas con la encriptación", 'WARNING');
                    $this->addResult('Encryption', 'MEDIUM', 'Problemas con encriptación de datos');
                }
            } else {
                $this->log("  Constantes de encriptación no definidas", 'WARNING');
                $this->addResult('Encryption', 'MEDIUM', 'Constantes de encriptación no definidas');
            }
        } catch (\Exception $e) {
            $this->log("  Error en encriptación: " . $e->getMessage(), 'WARNING');
            $this->addResult('Encryption', 'HIGH', 'Error en sistema de encriptación: ' . $e->getMessage());
        }
    }
    
    /**
     * Realizar petición HTTP
     */
    private function makeRequest($method, $endpoint, $data = null) {
        $url = $this->baseUrl . $endpoint;
        $ch = curl_init($url);
        
$options = [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST => $method,
    CURLOPT_HEADER => true,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'X-Security-Test: true'
    ]
];
if ($data && in_array($method, ['POST', 'PUT', 'PATCH'])) {
    $options[CURLOPT_POSTFIELDS] = json_encode($data);
}
        
        curl_setopt_array($ch, $options);
        
        $response = curl_exec($ch);
        
        if (curl_errno($ch)) {
            $this->log("  Error curl en $endpoint: " . curl_error($ch), 'WARNING');
            curl_close($ch);
            return null;
        }
        
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $body = substr($response, $headerSize);
        $this->lastHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        curl_close($ch);
        
        $decoded = json_decode($body, true);
        return $decoded !== null ? $decoded : $body;
    }
    
    private function getLastHttpCode() {
        return $this->lastHttpCode ?? 0;
    }
    
    private function containsSQLError($response) {
        $indicators = [
            'mysql_fetch', 'SQL syntax', 'You have an error',
            'Unclosed quotation mark', 'PDOException', 'SQLSTATE',
            'MySQL server', 'mysqli_error', 'Database error',
            'SQL', 'mysql_'
        ];
        
        $responseStr = is_array($response) ? json_encode($response) : (string)$response;
        
        foreach ($indicators as $indicator) {
            if (stripos($responseStr, $indicator) !== false) {
                return true;
            }
        }
        return false;
    }
    
    private function addResult($category, $severity, $message) {
        $this->testResults[] = [
            'category' => $category,
            'severity' => $severity,
            'message' => $message,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
    
    private function log($message, $type = 'INFO') {
        $colors = [
            'INFO' => "\033[36m",
            'TEST' => "\033[33m",
            'WARNING' => "\033[33m",
            'HIGH' => "\033[31m",
            'CRITICAL' => "\033[41m",
            'MEDIUM' => "\033[35m",
            'SUCCESS' => "\033[32m"
        ];
        
        $reset = "\033[0m";
        $color = $colors[$type] ?? "\033[0m";
        
        echo $color . $message . $reset . "\n";
    }
    
    private function generateReport() {
        $filename = __DIR__ . '/security_report_' . date('Y-m-d_H-i-s') . '.html';
        
        $html = '<!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <title>Reporte de Seguridad API - Máquinas Recreativas</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
                .container { max-width: 1200px; margin: auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
                h1 { color: #333; border-bottom: 2px solid #333; padding-bottom: 10px; }
                h2 { color: #555; margin-top: 30px; }
                .summary { margin: 20px 0; padding: 15px; background: #f8f9fa; border-radius: 8px; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
                th { background: #333; color: white; }
                .critical { background: #dc3545; color: white; padding: 4px 8px; border-radius: 4px; display: inline-block; }
                .high { background: #fd7e14; color: white; padding: 4px 8px; border-radius: 4px; display: inline-block; }
                .medium { background: #ffc107; padding: 4px 8px; border-radius: 4px; display: inline-block; }
                .timestamp { color: #666; font-size: 0.9em; }
                .severity-cell { text-align: center; }
            </style>
        </head>
        <body>
            <div class="container">
                <h1>Reporte de Auditoría de Seguridad</h1>
                <p><strong>Fecha:</strong> ' . date('Y-m-d H:i:s') . '</p>
                <p><strong>URL Base:</strong> ' . htmlspecialchars($this->baseUrl) . '</p>
                <div class="summary">
                    <p><strong>Vulnerabilidades encontradas:</strong> ' . count($this->testResults) . '</p>
                </div>';
        
        if (count($this->testResults) > 0) {
            $html .= '<h2>Detalle de Vulnerabilidades</h2>
            <table>
                <thead>
                    <tr>
                        <th>Categoría</th>
                        <th>Severidad</th>
                        <th>Descripción</th>
                        <th>Timestamp</th>
                    </tr>
                </thead>
                <tbody>';
            
            foreach ($this->testResults as $result) {
                $severityClass = strtolower($result['severity']);
                $severityText = $result['severity'];
                $html .= "<tr>
                    <td>" . htmlspecialchars($result['category']) . "</td>
                    <td class='severity-cell'><span class='{$severityClass}'>{$severityText}</span></td>
                    <td>" . htmlspecialchars($result['message']) . "</td>
                    <td class='timestamp'>" . htmlspecialchars($result['timestamp']) . "</td>
                </tr>";
            }
            
            $html .= '</tbody>
            </table>';
        } else {
            $html .= '<p class="success" style="color: #28a745; font-weight: bold;"> No se encontraron vulnerabilidades</p>';
        }
        
        $html .= '</div></body></html>';
        
        file_put_contents($filename, $html);
        $this->log("\n Reporte guardado en: $filename", 'SUCCESS');
    }
}

// Verificar que el servidor está corriendo antes de ejecutar
echo "\n🔍 Verificando servidor...\n";

$ch = curl_init('http://localhost:8000/health');
curl_setopt($ch, CURLOPT_TIMEOUT, 3);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 0) {
    echo "\n ERROR: El servidor no está corriendo en http://localhost:8000\n";
    echo "Inicia el servidor con: php -S localhost:8000 -t public\n";
    exit(1);
}

echo " Servidor disponible (código $httpCode)\n";

// Ejecutar pruebas
echo "\n INICIANDO SCRIPT DE PRUEBAS DE SEGURIDAD\n";
echo "==========================================\n\n";

$tester = new APISecurityTest();
$tester->runAllTests();

echo "\n Pruebas completadas\n";