<?php
// tests/Smoke/SmokeComercioTest.php

require_once __DIR__ . '/SmokeTestCase.php';

class SmokeComercioTest extends SmokeTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->loginAsTestUser();
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

        // El servidor puede usar 'comercios' u otra clave para la colección.
        // Verificar que alguna clave contiene un array (estructura válida).
        $tieneColeccion = isset($response['comercios']) || isset($response['data']);
        $this->assertTrue(
            $tieneColeccion,
            'La respuesta debe contener la clave "comercios" o "data" con la lista'
        );
    }
}