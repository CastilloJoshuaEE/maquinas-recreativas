<?php
use PHPUnit\Framework\TestCase;

class SmokeTestCase extends TestCase
{
    protected string $baseUrl = 'http://localhost:8000';
    protected ?array $sessionCookies = null;
    protected ?array $testUser = null;
    protected ?string $testUserId = null;
    
    //  DECLARAR PROPIEDADES EXPLÍCITAMENTE (esto elimina los deprecations)
    protected ?string $lastResponseHeaders = null;
    protected int $lastHttpCode = 0;

    /**
     * Realiza login con un usuario de prueba (se crea si no existe)
     */
    protected function loginAsTestUser(): void
    {
        // Datos del usuario de prueba
        $this->testUser = [
            'nombre' => 'Test',
            'apellido' => 'User',
            'ci' => '99999999' . rand(10, 99),
            'email' => 'test_' . uniqid() . '@smoke.com',
            'usuario_asignado' => 'smoke_' . substr(uniqid(), -8),
            'contrasena' => 'Test123456',
            'tipo' => 'Administrador',
            'estado' => 'Activo'
        ];

        // Intentar registrar el usuario (puede fallar si ya existe)
        $registerResponse = $this->makeRequest('POST', '/usuario/register', $this->testUser);
        
        // Hacer login
        $loginResponse = $this->makeRequest('POST', '/usuario/login', [
            'usuario_asignado' => $this->testUser['usuario_asignado'],
            'contrasena' => $this->testUser['contrasena']
        ]);

        if (isset($loginResponse['success']) && $loginResponse['success']) {
            $this->testUserId = $loginResponse['usuario']['ID_Usuario'] ?? null;
            
            // Extraer cookies de sesión de la respuesta
            $this->extractSessionCookies();
            
            $this->assertNotNull($this->testUserId, 'No se pudo obtener ID de usuario');
        } else {
            $this->fail('No se pudo autenticar usuario de prueba');
        }
    }

    /**
     * Extrae las cookies de sesión de la última respuesta
     */
    protected function extractSessionCookies(): void
    {
        if ($this->lastResponseHeaders !== null) {
            preg_match_all('/^Set-Cookie:\s*([^;]+)/mi', $this->lastResponseHeaders, $matches);
            $this->sessionCookies = $matches[1] ?? [];
        }
    }

    /**
     * Realiza una petición HTTP con manejo de cookies
     */
    protected function makeRequest(string $method, string $endpoint, array $data = null): array
    {
        $url = $this->baseUrl . $endpoint;
        $ch = curl_init($url);

        $headers = ['Content-Type: application/json'];
        
        // Añadir cookies de sesión si existen
        if ($this->sessionCookies) {
            $headers[] = 'Cookie: ' . implode('; ', $this->sessionCookies);
        }

        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HEADER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_HTTPHEADER => $headers
        ];

        if ($data && in_array($method, ['POST', 'PUT', 'PATCH'])) {
            $options[CURLOPT_POSTFIELDS] = json_encode($data);
        }

        curl_setopt_array($ch, $options);

        $response = curl_exec($ch);
        
        if ($response === false) {
            $this->lastHttpCode = 0;
            $this->lastResponseHeaders = '';
            curl_close($ch);
            return ['error' => 'Curl error: ' . curl_error($ch)];
        }
        
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $this->lastResponseHeaders = substr($response, 0, $headerSize);
        $body = substr($response, $headerSize);
        $this->lastHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        return json_decode($body, true) ?? ['error' => 'Invalid response'];
    }

    protected function getLastHttpCode(): int
    {
        return $this->lastHttpCode;
    }

    /**
     * Resetea el estado entre pruebas
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        // No es necesario resetear las propiedades porque se recrean en cada test
    }
}