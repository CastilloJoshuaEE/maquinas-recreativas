<?php
// tests/Smoke/SmokeReporteTest.php

require_once __DIR__ . '/SmokeTestCase.php';

class SmokeReporteTest extends SmokeTestCase
{
    private ?string $otroUsuarioId = null;

    protected function setUp(): void
    {
        parent::setUp();

        // Usar registerAndLoginTestUser en lugar de loginAsTestUser
        if (!$this->registerAndLoginTestUser()) {
            $this->markTestSkipped('No se pudo autenticar usuario de prueba');
        }

        // Crear un segundo usuario que actúe como destinatario
        // Usar createTestUserData() en lugar de createTestUser()
        $otroUserData = $this->createTestUserData();
        
        // Primero login como admin
        $this->loginAsAdmin();
        
        $response = $this->makeRequest('POST', '/administrador/usuarios', $otroUserData);
        if ($this->isSuccessResponse($response)) {
            $this->otroUsuarioId = $response['id'] ?? null;
            $this->testUserUsername = $response['usuario_asignado'] ?? null;
        }
        
        // Volver a loguear como el usuario principal
        $this->registerAndLoginTestUser();
    }

    /** @test */
    public function sePuedeCrearUnReporte()
    {
        $this->assertNotNull($this->otroUsuarioId, 'No se pudo crear usuario destinatario');

        $response = $this->makeRequest('POST', '/reportes/crear', [
            'descripcion'          => 'Reporte de prueba smoke test',
            'idUsuarioDestinatario'=> $this->otroUsuarioId,
        ]);

        $this->assertEquals(201, $this->getLastHttpCode());
        $this->assertTrue($this->isSuccessResponse($response));
        $this->assertArrayHasKey('id', $response);
    }

    /**
     * @test
     */
    public function sePuedeCrearUnComentarioEnUnReporte()
    {
        // Crear reporte propio
        $reporteResponse = $this->makeRequest('POST', '/reportes/crear', [
            'descripcion'          => 'Reporte para comentario smoke',
            'idUsuarioDestinatario'=> $this->otroUsuarioId,
        ]);

        if (!$this->isSuccessResponse($reporteResponse)) {
            $this->markTestSkipped(
                'No se pudo crear el reporte base para el comentario: '
                . json_encode($reporteResponse)
            );
            return;
        }

        $reporteId = $reporteResponse['id'];

        $response = $this->makeRequest('POST', '/comentarios', [
            'idReporte'  => $reporteId,
            'comentario' => 'Comentario de prueba smoke test',
        ]);

        $this->assertEquals(201, $this->getLastHttpCode());
        $this->assertTrue($this->isSuccessResponse($response));
        $this->assertArrayHasKey('id', $response);
    }

    /** @test */
    public function sePuedenObtenerReportesPorUsuario()
    {
        $response = $this->makeRequest('GET', "/reportes/usuario/{$this->testUserId}");

        $this->assertEquals(200, $this->getLastHttpCode());
        $this->assertTrue($this->isSuccessResponse($response));
        $this->assertArrayHasKey('reportes', $response);
        $this->assertIsArray($response['reportes']);
    }

    /** @test */
    public function sePuedeActualizarEstadoDeUnReporte()
    {
        $response = $this->makeRequest('POST', '/reportes/crear', [
            'descripcion' => 'Reporte para cambiar estado',
        ]);

        if (!$this->isSuccessResponse($response)) {
            $this->markTestSkipped('No se pudo crear reporte para probar cambio de estado');
            return;
        }

        $reporteId = $response['id'];

        $response = $this->makeRequest('PUT', "/reportes/{$reporteId}/estado", [
            'estado' => 'En proceso',
        ]);

        $this->assertEquals(200, $this->getLastHttpCode());
        $this->assertTrue($this->isSuccessResponse($response));
    }

    /** @test */
    public function noSePuedeCrearReporteSinDescripcion()
    {
        $response = $this->makeRequest('POST', '/reportes/crear', []);

        $this->assertEquals(200, $this->getLastHttpCode());
        $this->assertFalse(
            $this->isSuccessResponse($response),
            'Crear un reporte sin descripción no debe devolver success:true'
        );
    }
}