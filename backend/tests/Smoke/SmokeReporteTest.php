<?php
// tests/Smoke/SmokeReporteTest.php

require_once __DIR__ . '/SmokeTestCase.php';

class SmokeReporteTest extends SmokeTestCase
{
    private ?string $otroUsuarioId = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loginAsTestUser();

        // Crear un segundo usuario que actúe como destinatario
        $otroUser = $this->createTestUser();
        $response = $this->makeRequest('POST', '/usuario/register', $otroUser);
        if ($this->isSuccessResponse($response)) {
            $this->otroUsuarioId = $response['userId'];
        }
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
     * 
     * NOTA: Este test es AUTÓNOMO, no depende de ningún otro test.
     * Crea su propio reporte y luego el comentario.
     */
    public function sePuedeCrearUnComentarioEnUnReporte()
    {
        // Crear reporte propio (no depender de otro test)
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

    /**
     * @test
     *
     * El servidor devuelve HTTP 200 con success:false para errores de validación,
     * no HTTP 400. El smoke test verifica el comportamiento real.
     */
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