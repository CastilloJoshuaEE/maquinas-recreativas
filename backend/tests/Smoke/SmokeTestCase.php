<?php
// tests/Smoke/SmokeTestCase.php

use PHPUnit\Framework\TestCase;
use maquinas_recreativas\Tests\TestDatabase;

class SmokeTestCase extends TestCase
{
    protected string $baseUrl = 'http://localhost:8000';
    protected ?string $testUserId = null;
    protected ?string $testUserUsername = null;

    protected int $lastHttpCode = 0;
    protected ?string $lastResponseHeaders = null;
    protected array $lastResponse = [];

    private string $cookieFile;
    private static ?TestDatabase $testDb = null;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        cleanTestDatabase();
        static::resetServerRateLimits();
        
        self::$testDb = TestDatabase::getInstance();
    }

    protected static function resetServerRateLimits(): void
    {
        $ch = curl_init('http://localhost:8000/reset-rate-limits');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_exec($ch);
        curl_close($ch);
    }

    protected function setUp(): void
    {
        parent::setUp();
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

    /**
     * Crear datos de usuario para pruebas (sin contraseña - será usada por admin)
     */
    protected function createTestUserData(): array
    {
        $timestamp = time() . '_' . uniqid();
        return [
            'nombre'   => 'Smoke',
            'apellido' => 'Tester',
            'ci'       => (string)rand(10000000, 89999999) . rand(100, 999),
            'email'    => "smoke_{$timestamp}@test.com",
            'contrasena' => 'Password123!',  // <-- AÑADIR CONTRASEÑA
            'tipo'     => 'Usuario'
        ];
    }

    /**
     * Crear datos de usuario técnico para pruebas
     */
    protected function createTecnicoUserData(string $especialidad = 'Ensamblador'): array
    {
        $timestamp = time() . '_' . uniqid();
        return [
            'nombre'      => 'Tecnico',
            'apellido'    => 'Smoke',
            'ci'          => (string)rand(10000000, 89999999) . rand(100, 999),
            'email'       => "tecnico_{$especialidad}_{$timestamp}@test.com",
            'contrasena'  => 'Password123!',
            'tipo'        => 'Tecnico',
            'especialidad'=> $especialidad
        ];
    }

    /**
     * Login como técnico ensamblador (crea el usuario si no existe)
     */
    protected function loginAsTecnicoEnsamblador(): bool
    {
        // Primero login como admin para poder crear usuarios
        if (!$this->loginAsAdmin()) {
            return false;
        }
        
        $userData = $this->createTecnicoUserData('Ensamblador');
        
        // Registrar usuario usando endpoint de administrador
        $registerResponse = $this->makeRequest('POST', '/administrador/usuarios', $userData);
        
        if (!$this->isSuccessResponse($registerResponse)) {
            $this->markTestSkipped('No se pudo registrar técnico ensamblador: ' . json_encode($registerResponse));
            return false;
        }
        
        $this->testUserId = $registerResponse['id'] ?? null;
        $this->testUserUsername = $registerResponse['usuario_asignado'] ?? null;
        
        // Cerrar sesión de admin
        $this->makeRequest('POST', '/usuario/logout', []);
        $this->clearCookies();
        
        // Login como el técnico
        $loginResponse = $this->makeRequest('POST', '/usuario/login', [
            'usuario_asignado' => $this->testUserUsername,
            'contrasena' => 'Password123!'
        ]);
        
        return $this->isSuccessResponse($loginResponse);
    }

    /**
     * Registrar y loguear un usuario usando el endpoint de administrador (estado Activo)
     */
    protected function registerAndLoginTestUser(): bool
{
    // Primero, login como administrador del sistema
    if (!$this->loginAsAdmin()) {
        $this->markTestSkipped('No se pudo iniciar sesión como administrador');
        return false;
    }
    
    $userData = $this->createTestUserData();
    
    // Registrar usuario usando endpoint de administrador
    $registerResponse = $this->makeRequest('POST', '/administrador/usuarios', $userData);
    
    if (!$this->isSuccessResponse($registerResponse)) {
        $this->markTestSkipped('No se pudo registrar usuario de prueba: ' . json_encode($registerResponse));
        return false;
    }
    
    $this->testUserId = $registerResponse['id'] ?? null;
    
    // Obtener el usuario_asignado del usuario creado (NO viene en la respuesta del POST)
    $usersResponse = $this->makeRequest('GET', '/administrador/usuarios');
    if ($this->isSuccessResponse($usersResponse)) {
        $usuarios = $usersResponse['usuarios'] ?? [];
        foreach ($usuarios as $usuario) {
            if ($usuario['id'] === $this->testUserId) {
                $this->testUserUsername = $usuario['usuario_asignado'] ?? null;
                break;
            }
        }
    }
    
    // Cerrar sesión de admin
    $this->makeRequest('POST', '/usuario/logout', []);
    $this->clearCookies();
    
    // Login como el nuevo usuario
    if ($this->testUserUsername) {
        $loginResponse = $this->makeRequest('POST', '/usuario/login', [
            'usuario_asignado' => $this->testUserUsername,
            'contrasena' => 'Password123!'
        ]);
        
        if ($this->isSuccessResponse($loginResponse)) {
            return true;
        }
    }
    
    $this->markTestSkipped('No se pudo loguear usuario de prueba');
    return false;
}
    /**
     * Login como administrador del sistema
     */
    protected function loginAsAdmin(): bool
    {
        // Usar el admin por defecto de TestDatabase
        $response = $this->makeRequest('POST', '/usuario/login', [
            'usuario_asignado' => 'admin_test',
            'contrasena' => 'admin123'
        ]);
        
        return $this->isSuccessResponse($response);
    }

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
            $this->lastHttpCode        = 0;
            $this->lastResponseHeaders = '';
            $this->lastResponse        = ['error' => 'Curl error: ' . curl_error($ch)];
            curl_close($ch);
            return $this->lastResponse;
        }

        $headerSize                = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $this->lastResponseHeaders = substr($response, 0, $headerSize);
        $body                      = substr($response, $headerSize);
        $this->lastHttpCode        = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $decoded            = json_decode($body, true);
        $this->lastResponse = $decoded ?? ['raw_body' => $body];
        return $this->lastResponse;
    }

    protected function isSuccessResponse(array $response): bool
    {
        return isset($response['success']) && $response['success'] === true;
    }

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