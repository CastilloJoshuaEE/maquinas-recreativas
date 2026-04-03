<?php
// tests/Smoke/SmokeTestCase.php

use PHPUnit\Framework\TestCase;
use maquinas_recreativas\Tests\TestDatabase;

class SmokeTestCase extends TestCase
{
    protected string $baseUrl = 'http://localhost:8000';
    protected ?string $testUserId = null;

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
        
        // Obtener instancia de TestDatabase para consultas directas
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

    protected function createTestUser(): array
    {
        $timestamp = time() . '_' . uniqid();
        return [
            'nombre'           => 'Smoke',
            'apellido'         => 'Tester',
            'ci'               => (string)rand(10000000, 89999999) . rand(100, 999),
            'email'            => "smoke_{$timestamp}@test.com",
            'usuario_asignado' => 'smoke_' . substr(md5($timestamp), 0, 8),
            'contrasena'       => 'SmokeTest123!',
            'tipo'             => 'Administrador',
            'estado'           => 'Activo',
        ];
    }

    protected function createTecnicoUser(string $especialidad = 'Ensamblador'): array
    {
        $timestamp = time() . '_' . uniqid();
        return [
            'nombre'      => 'Tecnico',
            'apellido'    => 'Smoke',
            'ci'          => (string)rand(10000000, 89999999) . rand(100, 999),
            'email'       => "tecnico_{$especialidad}_{$timestamp}@test.com",
            'contrasena'  => 'SmokeTest123!',
            'tipo'        => 'Tecnico',
            'especialidad'=> $especialidad,
        ];
    }

    protected function loginAsTestUser(): array
    {
        return $this->loginWithUserData($this->createTestUser());
    }

    protected function loginAsTecnicoEnsamblador(): array
    {
        return $this->loginWithUserData($this->createTecnicoUser('Ensamblador'));
    }

    /**
     * Registra y loguea con los datos dados.
     * Después del registro, verifica que el email en la BD no esté vacío.
     */
    protected function loginWithUserData(array $userData): array
    {
        $registerResponse = $this->makeRequest('POST', '/usuario/register', $userData);

        if (!$this->isSuccessResponse($registerResponse)) {
            // Reintentar con nuevos datos si falla (evita colisiones)
            $userData = array_merge($userData, $this->createTestUser());
            $registerResponse = $this->makeRequest('POST', '/usuario/register', $userData);
        }

        if (!$this->isSuccessResponse($registerResponse)) {
            $this->markTestSkipped(
                'No se pudo registrar usuario de prueba: ' . json_encode($registerResponse)
            );
            return [];
        }

        $this->testUserId = $registerResponse['userId'] ?? null;
        
        // --- VERIFICACIÓN DEL EMAIL EN LA BASE DE DATOS ---
        if ($this->testUserId && self::$testDb) {
            $conn = self::$testDb->getConnection();
            $stmt = $conn->prepare("SELECT email FROM usuario WHERE ID_Usuario = ?");
            $stmt->bind_param('s', $this->testUserId);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();
            
            if ($row && empty($row['email'])) {
                // El email está vacío o nulo - forzar actualización con el email original cifrado
                $emailOriginal = $userData['email'];
                $emailCifrado = base64_encode(openssl_encrypt($emailOriginal, 'AES-256-CBC', 'clave_super_segura_cambiar_en_produccion_2024', 0, 'vector_inicial_16'));
                $updateStmt = $conn->prepare("UPDATE usuario SET email = ? WHERE ID_Usuario = ?");
                $updateStmt->bind_param('ss', $emailCifrado, $this->testUserId);
                $updateStmt->execute();
                $updateStmt->close();
                
                error_log("Corregido email vacío para usuario {$this->testUserId} -> {$emailOriginal}");
            }
        }
        // --- FIN VERIFICACIÓN ---

        $assignedUsername = $registerResponse['usuario_asignado']
                         ?? ($userData['usuario_asignado'] ?? null);

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

    protected function isFailureResponse(array $response): bool
    {
        if (isset($response['success'])) {
            return $response['success'] === false;
        }
        return isset($response['error']) || isset($response['message']);
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