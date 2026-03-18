<?php
require_once __DIR__ . '/SmokeTestCase.php';

class SmokeUsuarioTest extends SmokeTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Login antes de cada prueba que lo necesite
        $this->loginAsTestUser();
    }

    /**
     * @test
     */
    public function elEndpointHealthResponde()
    {
        $response = $this->makeRequest('GET', '/health');
        
        $this->assertEquals(200, $this->getLastHttpCode());
        $this->assertIsArray($response);
        $this->assertArrayHasKey('status', $response);
        $this->assertEquals('ok', $response['status']);
    }

    /**
     * @test
     */
    public function elEndpointTestDbResponde()
    {
        $response = $this->makeRequest('GET', '/test-db');

        $this->assertEquals(200, $this->getLastHttpCode());
        $this->assertIsArray($response);
        $this->assertArrayHasKey('success', $response);
        $this->assertTrue($response['success']);
    }

    /**
     * @test
     */
    public function elLoginRespondeConCredencialesInvalidas()
    {
        $response = $this->makeRequest('POST', '/usuario/login', [
            "usuario_asignado" => "usuario_que_no_existe",
            "contrasena" => "contraseña_incorrecta"
        ]);

        $this->assertIsArray($response);
        $this->assertArrayHasKey('success', $response);
        $this->assertFalse($response['success']);
        $this->assertArrayHasKey('message', $response);
    }
}