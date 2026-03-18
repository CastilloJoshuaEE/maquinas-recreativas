<?php
/**
 * Security Test Script for API
 * Ejecutar: php security-test.php
 */

class APISecurityTest {
    private $baseUrl = 'http://localhost:8000';
    private $endpoints = [];
    private $testResults = [];
    private $sessionToken = null;
    private $testUserId = null;
    private $authBypassPayloads = [];
    
    // Payloads para pruebas
    private $sqlPayloads = [
        "' OR '1'='1",
        "'; DROP TABLE users; --",
        "' UNION SELECT * FROM users--",
        "' OR 1=1--",
        "admin'--",
        "' OR '1'='1'/*",
        "' OR 1=1#",
        "1' ORDER BY 10--",
        "1' AND SLEEP(5)--",
        "'; WAITFOR DELAY '00:00:05'--"
    ];
    
    private $xssPayloads = [
        "<script>alert('XSS')</script>",
        "<img src=x onerror=alert('XSS')>",
        "<svg onload=alert('XSS')>",
        "javascript:alert('XSS')",
        "\"><script>alert('XSS')</script>",
        "'><script>alert('XSS')</script>",
        "<img src=\"javascript:alert('XSS')\">"
    ];
    

public function __construct() {

    $this->authBypassPayloads = [
        ['Authorization' => 'Bearer invalid-token'],
        ['Authorization' => 'Basic ' . base64_encode('admin:admin')],
        ['X-Forwarded-For' => '127.0.0.1'],
        ['Cookie' => 'PHPSESSID=../../../etc/passwd'],
        ['Authorization' => 'Bearer ' . str_repeat('A', 1000)]
    ];

    $this->discoverEndpoints();
}
    
    /**
     * Descubre todos los endpoints del routes.php
     */
    private function discoverEndpoints() {
        $routesFile = __DIR__ . '/../../routes.php';
        if (!file_exists($routesFile)) {
            die("Error: No se encuentra el archivo routes.php\n");
        }
        
        $content = file_get_contents($routesFile);
        
        // Patrón para encontrar rutas
        $patterns = [
            "/case\s+'([^']+)':/",
            "/case\s+\(preg_match\('([^']+)'[^)]+\)[^:]+:/",
            "/case\s+'([^']+)':/i"
        ];
        
        foreach ($patterns as $pattern) {
            preg_match_all($pattern, $content, $matches);
            if (!empty($matches[1])) {
                foreach ($matches[1] as $endpoint) {
                    // Limpiar endpoints con patrones regex
                    $cleanEndpoint = preg_replace('/\/\([^)]+\)/', '/{param}', $endpoint);
                    $cleanEndpoint = preg_replace('/\\\/', '', $cleanEndpoint);
                    $cleanEndpoint = preg_replace('/\^/', '', $cleanEndpoint);
                    $cleanEndpoint = preg_replace('/\$/', '', $cleanEndpoint);
                    
                    if (!in_array($cleanEndpoint, $this->endpoints)) {
                        $this->endpoints[] = $cleanEndpoint;
                    }
                }
            }
        }
        
        echo "📡 Endpoints descubiertos: " . count($this->endpoints) . "\n";
    }
    
    /**
     * Ejecutar todas las pruebas
     */
    public function runAllTests() {
        $this->log("🔐 INICIANDO AUDITORÍA DE SEGURIDAD", 'INFO');
        $this->log("====================================\n", 'INFO');
        
        // 1. Probar configuración CORS
        $this->testCORSConfiguration();
        
        // 2. Login para obtener token
        $this->testLogin();
        
        // 3. Probar SQL Injection en endpoints POST
        $this->testSQLInjection();
        
        // 4. Probar XSS en endpoints
        $this->testXSS();
        
        // 5. Probar bypass de autenticación
        $this->testAuthBypass();
        
        // 6. Probar endpoints inseguros
        $this->testInsecureEndpoints();
        
        // 7. Probar validaciones faltantes
        $this->testMissingValidations();
        
        // 8. Probar protección de sesiones
        $this->testSessionProtection();
        
        // 9. Probar rate limiting
        $this->testRateLimiting();
        
        // 10. Probar exposición de datos sensibles
        $this->testDataExposure();
        
        // Generar reporte
        $this->generateReport();
    }
    
    /**
     * Probar configuración CORS
     */
    private function testCORSConfiguration() {
        $this->log("\n📌 TEST: Configuración CORS", 'TEST');
        
        $testOrigins = [
            'http://evil.com',
            'http://localhost:3000',
            'null',
            'https://malicious-site.com'
        ];
        
        foreach ($testOrigins as $origin) {
            $ch = curl_init($this->baseUrl . '/health');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Origin: ' . $origin,
                'Access-Control-Request-Method: GET'
            ]);
            
            $response = curl_exec($ch);
            $headers = curl_getinfo($ch);
            
            if (isset($headers['access-control-allow-origin'])) {
                $allowed = $headers['access-control-allow-origin'];
                if ($allowed === '*' || $allowed === $origin) {
                    $this->log("⚠️  CORS permite origen no autorizado: $origin", 'WARNING');
                    $this->addResult('CORS', 'CRITICAL', "Permite origen: $origin");
                }
            }
            
            curl_close($ch);
        }
    }
    
    /**
     * Probar login y obtener token
     */
    private function testLogin() {
        $this->log("\n📌 TEST: Autenticación", 'TEST');
        
        // Intentar login con credenciales inválidas
        $loginData = [
            'usuario_asignado' => 'admin',
            'contrasena' => 'wrongpassword'
        ];
        
        $response = $this->makeRequest('POST', '/usuario/login', $loginData);
        
        if (isset($response['success']) && $response['success'] === true) {
            $this->log("⚠️  Login exitoso con credenciales incorrectas", 'WARNING');
            $this->addResult('Auth', 'CRITICAL', 'Login permite credenciales incorrectas');
        }
        
        // Intentar login con SQL injection
        foreach ($this->sqlPayloads as $payload) {
            $loginData = [
                'usuario_asignado' => $payload,
                'contrasena' => 'anypassword'
            ];
            
            $response = $this->makeRequest('POST', '/usuario/login', $loginData);
            
            if (isset($response['success']) && $response['success'] === true) {
                $this->log("⚠️  Posible SQL Injection en login con: $payload", 'CRITICAL');
                $this->addResult('SQL Injection', 'CRITICAL', "Login vulnerable con payload: $payload");
                break;
            }
        }
    }
    
    /**
     * Probar SQL Injection
     */
    private function testSQLInjection() {
        $this->log("\n📌 TEST: SQL Injection", 'TEST');
        
        $endpointsToTest = [
            '/usuario/profile/{param}',
            '/administrador/usuarios/{param}',
            '/reportes/usuario/{param}',
            '/componentes/en-uso/{param}'
        ];
        
        foreach ($endpointsToTest as $endpoint) {
            foreach ($this->sqlPayloads as $payload) {
                $url = str_replace('{param}', urlencode($payload), $endpoint);
                $response = $this->makeRequest('GET', $url);
                
                // Buscar indicadores de SQL injection
                if (is_string($response)) {
                    $indicators = [
                        'mysql_fetch',
                        'SQL syntax',
                        'You have an error',
                        'Unclosed quotation mark',
                        'Warning: mysql',
                        'PDOException',
                        'SQLSTATE'
                    ];
                    
                    foreach ($indicators as $indicator) {
                        if (stripos($response, $indicator) !== false) {
                            $this->log("⚠️  Posible SQL Injection en $endpoint con payload: $payload", 'CRITICAL');
                            $this->addResult('SQL Injection', 'CRITICAL', "Endpoint $endpoint vulnerable");
                            break 2;
                        }
                    }
                }
            }
        }
    }
    
    /**
     * Probar XSS
     */
    private function testXSS() {
        $this->log("\n📌 TEST: XSS (Cross-Site Scripting)", 'TEST');
        
        $endpointsWithInput = [
            '/comentarios' => 'POST',
            '/reportes/crear' => 'POST',
            '/maquina/register' => 'POST'
        ];
        
        foreach ($endpointsWithInput as $endpoint => $method) {
            foreach ($this->xssPayloads as $payload) {
                $data = [
                    'comentario' => $payload,
                    'descripcion' => $payload,
                    'nombre' => $payload,
                    'mensaje' => $payload
                ];
                
                $response = $this->makeRequest($method, $endpoint, $data);
                
                if (is_string($response) && strpos($response, $payload) !== false) {
                    $this->log("⚠️  Posible XSS en $endpoint - payload no sanitizado", 'HIGH');
                    $this->addResult('XSS', 'HIGH', "Endpoint $endpoint podría ser vulnerable a XSS");
                    break;
                }
            }
        }
    }
    
    /**
     * Probar bypass de autenticación
     */
    private function testAuthBypass() {
        $this->log("\n📌 TEST: Bypass de Autenticación", 'TEST');
        
        $protectedEndpoints = [
            '/administrador/usuarios',
            '/usuario/perfil',
            '/notificaciones/{param}',
            '/reportes/usuarios-chat'
        ];
        
        foreach ($protectedEndpoints as $endpoint) {
            foreach ($this->authBypassPayloads as $headers) {
                $ch = curl_init($this->baseUrl . $endpoint);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $this->formatHeaders($headers));
                curl_setopt($ch, CURLOPT_HEADER, true);
                
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                
                if ($httpCode === 200) {
                    $this->log("⚠️  Posible bypass de autenticación en $endpoint", 'CRITICAL');
                    $this->addResult('Auth Bypass', 'CRITICAL', "Endpoint $endpoint accesible sin autenticación");
                }
                
                curl_close($ch);
            }
        }
    }
    
    /**
     * Probar endpoints inseguros (métodos HTTP no permitidos)
     */
    private function testInsecureEndpoints() {
        $this->log("\n📌 TEST: Endpoints Inseguros", 'TEST');
        
        $testMethods = ['PUT', 'DELETE', 'PATCH', 'TRACE', 'OPTIONS'];
        
        foreach ($this->endpoints as $endpoint) {
            if (strpos($endpoint, '{param}') !== false) continue;
            
            foreach ($testMethods as $method) {
                $response = $this->makeRequest($method, $endpoint, []);
                $httpCode = $this->getLastHttpCode();
                
if (!in_array($httpCode, [401,403,404,405])) {                    
                    $this->log("⚠️  Endpoint $endpoint acepta método $method", 'MEDIUM');
                    $this->addResult('Insecure Endpoint', 'MEDIUM', "$endpoint acepta método $method no permitido");
                }
            }
        }
    }
    
    /**
     * Probar validaciones faltantes
     */
    private function testMissingValidations() {
        $this->log("\n📌 TEST: Validaciones Faltantes", 'TEST');
        
        $testCases = [
            '/usuario/register' => [
                ['nombre' => '', 'apellido' => '', 'email' => 'invalid'],
                ['nombre' => str_repeat('A', 1000)],
                ['email' => 'not-an-email'],
                ['contrasena' => '123']
            ],
            '/comercio/register' => [
                ['nombre' => '', 'tipo' => 'invalid'],
                ['telefono' => 'abc']
            ]
        ];
        
        foreach ($testCases as $endpoint => $cases) {
            foreach ($cases as $case) {
                $response = $this->makeRequest('POST', $endpoint, $case);
                
                if (isset($response['success']) && $response['success'] === true) {
                    $this->log("⚠️  Endpoint $endpoint aceptó datos inválidos", 'HIGH');
                    $this->addResult('Missing Validation', 'HIGH', "$endpoint aceptó datos inválidos");
                }
            }
        }
    }
    
    /**
     * Probar protección de sesiones
     */
    private function testSessionProtection() {
        $this->log("\n📌 TEST: Protección de Sesiones", 'TEST');
        
        // Obtener cookies de sesión
        $ch = curl_init($this->baseUrl . '/health');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        $response = curl_exec($ch);
        
        // Analizar cookies
        preg_match_all('/^Set-Cookie:\s*(.*)$/mi', $response, $cookies);
        
        foreach ($cookies[1] as $cookieHeader) {

    if (stripos($cookieHeader, 'PHPSESSID') !== false) {

        if (stripos($cookieHeader, 'httponly') === false) {
            $this->log("⚠️ Cookie de sesión sin HttpOnly", 'HIGH');
        }

        if (stripos($cookieHeader, 'secure') === false) {
            $this->log("⚠️ Cookie de sesión sin Secure flag", 'MEDIUM');
        }

    }
}
        
        curl_close($ch);
    }
    
    /**
     * Probar rate limiting
     */
    private function testRateLimiting() {
        $this->log("\n📌 TEST: Rate Limiting", 'TEST');
        
        $endpoint = '/usuario/login';
        $requests = 100;
        $successCount = 0;
        
        for ($i = 0; $i < $requests; $i++) {
            $response = $this->makeRequest('POST', $endpoint, [
                'usuario_asignado' => "user$i",
                'contrasena' => 'pass'
            ]);
            
            $httpCode = $this->getLastHttpCode();
            if ($httpCode === 200 || $httpCode === 401) {
                $successCount++;
            }
            
            if ($httpCode === 429) {
                $this->log("✅ Rate limiting detectado después de $i requests", 'INFO');
                break;
            }
        }
        
        if ($successCount === $requests) {
            $this->log("⚠️  No hay rate limiting implementado", 'MEDIUM');
            $this->addResult('Rate Limiting', 'MEDIUM', 'No se detectó rate limiting');
        }
    }
    
    /**
     * Probar exposición de datos sensibles
     */
    private function testDataExposure() {
        $this->log("\n📌 TEST: Exposición de Datos Sensibles", 'TEST');
        
        $sensitivePatterns = [
            '/\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}\b/' => 'Email',
            '/\b\d{8}[A-Z]\b/' => 'DNI/NIF',
            '/\b\d{16}\b/' => 'Tarjeta crédito',
            '/"contrasena"\s*:\s*"[^"]+"/' => 'Contraseña',
            '/"password"\s*:\s*"[^"]+"/' => 'Contraseña',
            '/\b\d{3}[-.]?\d{3}[-.]?\d{4}\b/' => 'Teléfono'
        ];
        
        $endpointsToCheck = ['/usuario/profile/{param}', '/administrador/usuarios'];
        
        foreach ($endpointsToCheck as $endpoint) {
            $url = str_replace('{param}', 'test-uuid', $endpoint);
            $response = $this->makeRequest('GET', $url);
            
            if (is_string($response)) {
                foreach ($sensitivePatterns as $pattern => $type) {
                    if (preg_match($pattern, $response)) {
                        $this->log("⚠️  Posible exposición de $type en $endpoint", 'CRITICAL');
                        $this->addResult('Data Exposure', 'CRITICAL', "$type expuesto en $endpoint");
                    }
                }
            }
        }
    }
    
    /**
     * Realizar petición HTTP
     */
    private function makeRequest($method, $endpoint, $data = null) {
        $ch = curl_init($this->baseUrl . $endpoint);
        
        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HEADER => true,
            CURLOPT_TIMEOUT => 10
        ];
        
        if ($data && in_array($method, ['POST', 'PUT', 'PATCH'])) {
            $options[CURLOPT_POSTFIELDS] = json_encode($data);
            $options[CURLOPT_HTTPHEADER] = ['Content-Type: application/json'];
        }
        
        curl_setopt_array($ch, $options);
        
        $response = curl_exec($ch);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $body = substr($response, $headerSize);
        
        $this->lastHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        curl_close($ch);
        
        return json_decode($body, true) ?? $body;
    }
    
    private function getLastHttpCode() {
        return $this->lastHttpCode ?? 0;
    }
    
    private function formatHeaders($headers) {
        $formatted = [];
        foreach ($headers as $key => $value) {
            $formatted[] = "$key: $value";
        }
        return $formatted;
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
            'INFO' => "\033[36m",     // Cyan
            'TEST' => "\033[33m",      // Yellow
            'WARNING' => "\033[33m",   // Yellow
            'HIGH' => "\033[31m",      // Red
            'CRITICAL' => "\033[41m",  // Red background
            'MEDIUM' => "\033[35m",    // Purple
            'SUCCESS' => "\033[32m"    // Green
        ];
        
        $reset = "\033[0m";
        $color = $colors[$type] ?? "\033[0m";
        
        echo $color . $message . $reset . "\n";
    }
    
    /**
     * Generar reporte final
     */
    private function generateReport() {
        $report = [
            'scan_date' => date('Y-m-d H:i:s'),
            'total_endpoints' => count($this->endpoints),
            'vulnerabilities_found' => count($this->testResults),
            'results' => $this->testResults,
            'summary' => [
                'CRITICAL' => 0,
                'HIGH' => 0,
                'MEDIUM' => 0,
                'LOW' => 0
            ]
        ];
        
        foreach ($this->testResults as $result) {
            $report['summary'][$result['severity']] = 
                ($report['summary'][$result['severity']] ?? 0) + 1;
        }
        
        // Guardar reporte en archivo
        $filename = 'security_report_' . date('Y-m-d_H-i-s') . '.html';
        $this->generateHTMLReport($report, $filename);
        
        // Mostrar resumen
        $this->log("\n📊 RESUMEN DE SEGURIDAD", 'INFO');
        $this->log("======================================", 'INFO');
        $this->log("Total endpoints: " . $report['total_endpoints'], 'INFO');
        $this->log("Vulnerabilidades encontradas: " . $report['vulnerabilities_found'], 
            $report['vulnerabilities_found'] > 0 ? 'CRITICAL' : 'SUCCESS');
        
        foreach ($report['summary'] as $severity => $count) {
            $color = $count > 0 ? ($severity === 'CRITICAL' ? 'CRITICAL' : ($severity === 'HIGH' ? 'HIGH' : 'MEDIUM')) : 'INFO';
            $this->log("  $severity: $count", $color);
        }
        
        $this->log("\n📄 Reporte detallado guardado en: $filename", 'SUCCESS');
    }
    
    /**
     * Generar reporte HTML
     */
    private function generateHTMLReport($report, $filename) {
        $html = <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Seguridad API</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .summary { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin: 20px 0; }
        .stat { padding: 20px; border-radius: 8px; color: white; text-align: center; }
        .CRITICAL { background: #dc3545; }
        .HIGH { background: #fd7e14; }
        .MEDIUM { background: #ffc107; color: #333; }
        .LOW { background: #28a745; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #333; color: white; }
        tr:hover { background: #f5f5f5; }
        .severity-badge { padding: 4px 8px; border-radius: 4px; color: white; font-weight: bold; }
        .timestamp { color: #666; font-size: 0.9em; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔐 Reporte de Auditoría de Seguridad</h1>
        <p>Fecha: {$report['scan_date']}</p>
        <p>Endpoints analizados: {$report['total_endpoints']}</p>
        
        <div class="summary">
            <div class="stat CRITICAL">CRITICAL<br>{$report['summary']['CRITICAL']}</div>
            <div class="stat HIGH">HIGH<br>{$report['summary']['HIGH']}</div>
            <div class="stat MEDIUM">MEDIUM<br>{$report['summary']['MEDIUM']}</div>
            <div class="stat LOW">LOW<br>{$report['summary']['LOW']}</div>
        </div>
        
        <h2>Vulnerabilidades Encontradas</h2>
HTML;
        
        if (empty($report['results'])) {
            $html .= "<p class='success'>✅ No se encontraron vulnerabilidades críticas</p>";
        } else {
            $html .= <<<HTML
        <table>
            <thead>
                <tr>
                    <th>Categoría</th>
                    <th>Severidad</th>
                    <th>Descripción</th>
                    <th>Timestamp</th>
                </tr>
            </thead>
            <tbody>
HTML;
            
            foreach ($report['results'] as $result) {
                $severity = $result['severity'];
                $html .= <<<HTML
                <tr>
                    <td>{$result['category']}</td>
                    <td><span class="severity-badge {$severity}">{$severity}</span></td>
                    <td>{$result['message']}</td>
                    <td class="timestamp">{$result['timestamp']}</td>
                </tr>
HTML;
            }
            
            $html .= <<<HTML
            </tbody>
        </table>
HTML;
        }
        
        $html .= <<<HTML
        
    </div>
</body>
</html>
HTML;
        
        file_put_contents($filename, $html);
    }
}

// Ejecutar pruebas
echo "\n🚀 INICIANDO SCRIPT DE PRUEBAS DE SEGURIDAD\n";
echo "==========================================\n\n";

$tester = new APISecurityTest();
$tester->runAllTests();

echo "\n✨ Pruebas completadas\n";
?>