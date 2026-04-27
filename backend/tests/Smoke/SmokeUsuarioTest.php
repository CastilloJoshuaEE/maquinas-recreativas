<?php
// tests/Smoke/SmokeUsuarioTest.php

require_once __DIR__ . '/SmokeTestCase.php';

class SmokeUsuarioTest extends SmokeTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Usar registerAndLoginTestUser en lugar de loginAsTestUser
        if (!$this->registerAndLoginTestUser()) {
            $this->markTestSkipped('No se pudo autenticar usuario de prueba');
        }
    }

    /** @test */
    public function elEndpointHealthResponde()
    {
        $response = $this->makeRequest('GET', '/health');

        $this->assertEquals(200, $this->getLastHttpCode());
        $this->assertIsArray($response);
        $this->assertArrayHasKey('status', $response);
        $this->assertEquals('ok', $response['status']);
    }

    /** @test */
    public function elEndpointTestDbResponde()
    {
        $response = $this->makeRequest('GET', '/test-db');

        $this->assertEquals(200, $this->getLastHttpCode());
        $this->assertIsArray($response);
        $this->assertTrue($this->isSuccessResponse($response));
    }

    /** @test */
    public function elLoginRespondeConCredencialesInvalidas()
    {
        $response = $this->makeRequest('POST', '/usuario/login', [
            'usuario_asignado' => 'usuario_que_no_existe',
            'contrasena'       => 'contraseña_incorrecta',
        ]);

        $this->assertIsArray($response);
        $this->assertFalse(
            $this->isSuccessResponse($response),
            'El login con credenciales inválidas no debe retornar success:true'
        );
    }
}