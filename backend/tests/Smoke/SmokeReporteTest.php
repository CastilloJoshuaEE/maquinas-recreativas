<?php
require_once __DIR__ . '/SmokeTestCase.php';

class SmokeReporteTest extends SmokeTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->loginAsTestUser();
    }

    /**
     * @test
     */
    public function elEndpointHealthResponde()
    {
        $response = $this->makeRequest('GET', '/health');
        $this->assertEquals(200, $this->getLastHttpCode());
    }

    /**
     * @test
     */
    public function elEndpointDeReportesRequiereDatos()
    {
        $response = $this->makeRequest('POST', '/reportes/crear', []);

        $this->assertEquals(400, $this->getLastHttpCode(), 
            "Debería responder 400 Bad Request con datos vacíos");
        
        $this->assertIsArray($response);
        $this->assertArrayHasKey('success', $response);
        $this->assertFalse($response['success']);
        $this->assertArrayHasKey('message', $response);
    }
}