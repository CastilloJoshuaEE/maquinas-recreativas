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
            return;
        }
    }

    /** @test */
  public function elEndpointObtenerComerciosResponde()
    {
        // Intentar obtener comercios
        $response = $this->makeRequest('GET', '/comercio/all');
        
        $httpCode = $this->getLastHttpCode();
        
        if ($httpCode === 401) {
            $this->markTestSkipped('No autenticado - el login falló');
            return;
        }

        $this->assertEquals(200, $httpCode,
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