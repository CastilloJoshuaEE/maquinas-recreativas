<?php
require_once __DIR__ . '/SmokeTestCase.php';

class SmokeComercioTest extends SmokeTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Login antes de cada prueba
        $this->loginAsTestUser();
    }

    /**
     * @test
     */
    public function elEndpointObtenerComerciosResponde()
    {
        $response = $this->makeRequest('GET', '/comercio/all');

        $this->assertEquals(200, $this->getLastHttpCode(), 
            "El endpoint /comercio/all debería responder 200 OK con autenticación");
        
        $this->assertIsArray($response);
        $this->assertArrayHasKey('success', $response);
        $this->assertTrue($response['success'], "La respuesta debería tener success=true");
        $this->assertArrayHasKey('comercios', $response);
        $this->assertIsArray($response['comercios']);
    }
}