<?php
// tests/Smoke/ReporteSmokeTest.php

require_once __DIR__ . '/SmokeTestCase.php';

class ReporteSmokeTest extends SmokeTestCase
{
    private ?string $reporteId = null;
    private ?string $otroUsuarioId = null;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Login como usuario principal
        $this->loginAsTestUser();
        
        // Crear otro usuario para tener destinatario
        $otroUser = $this->createTestUser();
        $response = $this->makeRequest('POST', '/usuario/register', $otroUser);
        if ($this->isSuccessResponse($response)) {
            $this->otroUsuarioId = $response['userId'];
        }
    }

    /**
     * @test
     */
    public function sePuedeCrearUnReporte()
    {
        $this->assertNotNull($this->otroUsuarioId, 'No se pudo crear usuario destinatario');
        
        $response = $this->makeRequest('POST', '/reportes/crear', [
            'descripcion' => 'Reporte de prueba smoke test',
            'idUsuarioDestinatario' => $this->otroUsuarioId
        ]);
        
        $this->assertEquals(201, $this->getLastHttpCode());
        $this->assertTrue($this->isSuccessResponse($response));
        $this->assertArrayHasKey('id', $response);
        
        $this->reporteId = $response['id'];
    }

    /**
     * @test
     * @depends sePuedeCrearUnReporte
     */
    public function sePuedeCrearUnComentarioEnUnReporte()
    {
        $this->assertNotNull($this->reporteId, 'Reporte no creado');
        
        $response = $this->makeRequest('POST', '/comentarios', [
            'idReporte' => $this->reporteId,
            'comentario' => 'Este es un comentario de prueba'
        ]);
        
        $this->assertEquals(201, $this->getLastHttpCode());
        $this->assertTrue($this->isSuccessResponse($response));
        $this->assertArrayHasKey('id', $response);
    }

    /**
     * @test
     */
    public function sePuedenObtenerReportesPorUsuario()
    {
        $response = $this->makeRequest('GET', "/reportes/usuario/{$this->testUserId}");
        
        $this->assertEquals(200, $this->getLastHttpCode());
        $this->assertTrue($this->isSuccessResponse($response));
        $this->assertArrayHasKey('reportes', $response);
        $this->assertIsArray($response['reportes']);
    }

    /**
     * @test
     */
    public function sePuedeActualizarEstadoDeUnReporte()
    {
        // Primero crear un reporte
        $response = $this->makeRequest('POST', '/reportes/crear', [
            'descripcion' => 'Reporte para cambiar estado'
        ]);
        
        if (!$this->isSuccessResponse($response)) {
            $this->markTestSkipped('No se pudo crear reporte para probar cambio de estado');
        }
        
        $reporteId = $response['id'];
        
        // Actualizar estado
        $response = $this->makeRequest('PUT', "/reportes/{$reporteId}/estado", [
            'estado' => 'En proceso'
        ]);
        
        $this->assertEquals(200, $this->getLastHttpCode());
        $this->assertTrue($this->isSuccessResponse($response));
    }

    /**
     * @test
     */
    public function noSePuedeCrearReporteSinDescripcion()
    {
        $response = $this->makeRequest('POST', '/reportes/crear', []);
        
        $this->assertEquals(400, $this->getLastHttpCode());
        $this->assertFalse($this->isSuccessResponse($response));
        $this->assertArrayHasKey('message', $response);
    }
}