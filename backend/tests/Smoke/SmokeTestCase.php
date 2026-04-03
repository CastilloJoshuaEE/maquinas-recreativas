<?php
// tests/Smoke/SmokeTestCase.php

use PHPUnit\Framework\TestCase;

class SmokeTestCase extends TestCase
{
    protected string $baseUrl = 'http://localhost:8000';
    protected ?string $testUserId = null;

    protected int $lastHttpCode = 0;
    protected ?string $lastResponseHeaders = null;
    protected array $lastResponse = [];

    // Archivo de cookies que cURL gestiona internamente (igual que en HttpTestCase)
    private string $cookieFile;

    /**
     * Se ejecuta UNA VEZ antes de todos los tests de la clase.
     * Limpia la BD para evitar errores de llave duplicada entre corridas.
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        cleanTestDatabase();
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Archivo de cookies fresco por instancia de test
        $this->cookieFile = sys_get_temp_dir() . '/smoke_cookies_' . uniqid() . '.txt';

        if (!$this->isServerRunning()) {
            $this->markTestSkipped(
                "El servidor no está respondiendo en {$this->baseUrl}. " .
                "Ejecuta 'php public/serve.php' en otra terminal."
            );
        }
    }

    protected function tearDown(): void
    {
        $this->clearCookies();
        parent::tearDown();
    }

    protected function isServerRunning(): bool
    {
        $ch = curl_init($this->baseUrl . '/health');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $httpCode !== 0;
    }

    protected function createTestUser(): array
    {
        $timestamp = time() . '_' . uniqid();
        return [
            'nombre'           => 'Smoke',
            'apellido'         => 'Tester',
            'ci'               => '99999999' . rand(10, 99),
            'email'            => "smoke_{$timestamp}@test.com",
            'usuario_asignado' => 'smoke_' . substr(md5($timestamp), 0, 8),
            'contrasena'       => 'SmokeTest123!',
            'tipo'             => 'Administrador',
            'estado'           => 'Activo',
        ];
    }
protected function loginAsTestUser(): array
{
    $userData = $this->createTestUser();

    $registerResponse = $this->makeRequest('POST', '/usuario/register', $userData);

    if (!$this->isSuccessResponse($registerResponse)) {
        $userData         = $this->createTestUser();
        $registerResponse = $this->makeRequest('POST', '/usuario/register', $userData);
    }

    if (!$this->isSuccessResponse($registerResponse)) {
        $this->markTestSkipped(
            'No se pudo registrar usuario de prueba: ' . json_encode($registerResponse)
        );
        return [];
    }

    $this->testUserId = $registerResponse['userId'] ?? null;

    
    $assignedUsername = $registerResponse['usuario_asignado'] ?? $userData['usuario_asignado'];

    $loginResponse = $this->makeRequest('POST', '/usuario/login', [
        'usuario_asignado' => $assignedUsername,
        'contrasena'       => $userData['contrasena'],
    ]);

    if (!$this->isSuccessResponse($loginResponse)) {
        $this->markTestSkipped(
            'No se pudo loguear usuario de prueba: ' . json_encode($loginResponse)
        );
        return [];
    }

    return $loginResponse['usuario'] ?? [];
}

    /**
     * Realiza una petición HTTP usando un archivo de cookies persistente.
     * cURL maneja el ciclo completo (Set-Cookie → Cookie) de forma nativa.
     */
    protected function makeRequest(string $method, string $endpoint, ?array $data = null): array
    {
        $url = $this->baseUrl . $endpoint;
        $ch  = curl_init($url);

        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_HEADER         => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Accept: application/json',
            ],
            CURLOPT_FOLLOWLOCATION => true,
            // El archivo de cookies reemplaza el manejo manual de Set-Cookie
            CURLOPT_COOKIEFILE     => $this->cookieFile,
            CURLOPT_COOKIEJAR      => $this->cookieFile,
            CURLOPT_SSL_VERIFYPEER => false,
        ];

        if ($data !== null && in_array($method, ['POST', 'PUT', 'PATCH'], true)) {
            $options[CURLOPT_POSTFIELDS] = json_encode($data);
        }

        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);

        if ($response === false) {
            $this->lastHttpCode      = 0;
            $this->lastResponseHeaders = '';
            $this->lastResponse      = ['error' => 'Curl error: ' . curl_error($ch)];
            curl_close($ch);
            return $this->lastResponse;
        }

        $headerSize              = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $this->lastResponseHeaders = substr($response, 0, $headerSize);
        $body                    = substr($response, $headerSize);
        $this->lastHttpCode      = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $decoded            = json_decode($body, true);
        $this->lastResponse = $decoded ?? ['raw_body' => $body];
        return $this->lastResponse;
    }

    protected function isSuccessResponse(array $response): bool
    {
        return isset($response['success']) && $response['success'] === true;
    }

    /**
     * Elimina el archivo de cookies (destruye la sesión del lado del cliente)
     * y crea uno nuevo vacío para el siguiente test.
     */
    protected function clearCookies(): void
    {
        if (isset($this->cookieFile) && file_exists($this->cookieFile)) {
            @unlink($this->cookieFile);
        }
        $this->cookieFile = sys_get_temp_dir() . '/smoke_cookies_' . uniqid() . '.txt';
    }

    protected function getLastHttpCode(): int
    {
        return $this->lastHttpCode;
    }
}