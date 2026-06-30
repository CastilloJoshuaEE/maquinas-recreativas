<?php
// tests/Smoke/SmokeComercioTest.php

require_once __DIR__ . '/SmokeTestCase.php';

class SmokeComercioTest extends SmokeTestCase
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
public function elEndpointObtenerComerciosResponde()
    {
        $response = $this->makeRequest('GET', '/comercio/all');

        $this->assertEquals(200, $this->getLastHttpCode(),
            'El endpoint /comercio/all debe responder HTTP 200 con autenticación');

        $this->assertIsArray($response);
        $this->assertTrue(
            $this->isSuccessResponse($response),
            'La respuesta debe indicar success:true'
        );

        $tieneColeccion = isset($response['comercios']) || isset($response['data']);
        $this->assertTrue(
            $tieneColeccion,
            'La respuesta debe contener la clave "comercios" o "data" con la lista'
        );
    }
}