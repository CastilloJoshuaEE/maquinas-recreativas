<?php
// tests/Smoke/SmokeMaquinaTest.php

require_once __DIR__ . '/SmokeTestCase.php';

class SmokeMaquinaTest extends SmokeTestCase
{
    private ?string $comercioId = null;
    private ?string $ensambladorId = null;
    private ?string $comprobadorId = null;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Crear y loguear como técnico ensamblador para generar placa y carcasa
        $this->loginAsTecnicoEnsamblador();
        
        // Guardar el ID del ensamblador para usarlo después
        $this->ensambladorId = $this->testUserId;

        // 2. Crear comercio
        $response = $this->makeRequest('POST', '/comercio/register', [
            'nombre'    => 'Comercio Smoke ' . time(),
            'tipo'      => 'Minorista',
            'direccion' => 'Calle Smoke 123',
            'telefono'  => '0999' . rand(100000, 999999),
        ]);

        if ($this->isSuccessResponse($response)) {
            $this->comercioId = $response['idComercio'] ?? null;
        }
        
        // 3. Crear un técnico comprobador para asignarlo a la máquina
        // Primero cerramos sesión del ensamblador
        $this->makeRequest('POST', '/usuario/logout', []);
        $this->clearCookies();
        
        // Creamos un usuario técnico comprobador
        $comprobadorUser = $this->createTecnicoUser('Comprobador');
        $registerResp = $this->makeRequest('POST', '/usuario/register', $comprobadorUser);
        
        if ($this->isSuccessResponse($registerResp)) {
            $this->comprobadorId = $registerResp['userId'];
        }
        
        // Volvemos a loguear como ensamblador para continuar
        $this->loginAsTecnicoEnsamblador();
    }

    /** @test */
    public function sePuedeGenerarUnaPlaca()
    {
        $response = $this->makeRequest('POST', '/maquina/generar-placa', []);

        $this->assertEquals(200, $this->getLastHttpCode());
        $this->assertTrue($this->isSuccessResponse($response));
        $this->assertArrayHasKey('placa', $response);
        $this->assertArrayHasKey('idComponente', $response);
    }

    /**
     * @test
     */
    public function sePuedeRegistrarUnaMaquina()
    {
        $this->assertNotNull($this->comercioId, 'Comercio no creado en setUp');
        $this->assertNotNull($this->ensambladorId, 'Ensamblador no creado en setUp');
        $this->assertNotNull($this->comprobadorId, 'Comprobador no creado en setUp');

        // Generar placa
        $placaResp = $this->makeRequest('POST', '/maquina/generar-placa', []);
        if (!$this->isSuccessResponse($placaResp)) {
            $this->markTestSkipped(
                'No se pudo generar la placa: ' . json_encode($placaResp)
            );
            return;
        }
        $placaId = $placaResp['idComponente'];
        $this->assertNotNull($placaId, 'No se recibió ID de placa');

        // Generar carcasa
        $carcasaResp = $this->makeRequest('POST', '/maquina/generar-placa', []);
        if (!$this->isSuccessResponse($carcasaResp)) {
            $this->markTestSkipped(
                'No se pudo generar la carcasa: ' . json_encode($carcasaResp)
            );
            return;
        }
        $carcasaId = $carcasaResp['idComponente'];
        $this->assertNotNull($carcasaId, 'No se recibió ID de carcasa');

        // Registrar la máquina - INCLUYENDO idEnsamblador e idComprobador
        $response = $this->makeRequest('POST', '/maquina/register', [
            'nombre'         => 'Maquina Smoke ' . time(),
            'tipo'           => 'Arcade',
            'idComercio'     => $this->comercioId,
            'idPlaca'        => $placaId,
            'idCarcasa'      => $carcasaId,
            'idEnsamblador'  => $this->ensambladorId,
            'idComprobador'  => $this->comprobadorId
        ]);

        // Verificar código HTTP y contenido
        $httpCode = $this->getLastHttpCode();
        if ($httpCode !== 201) {
            $errorMsg = isset($response['message']) ? $response['message'] : 
                       (isset($response['error']) ? $response['error'] : json_encode($response));
            $this->fail("Se esperaba código 201 pero se obtuvo {$httpCode}. Error: {$errorMsg}");
        }
        
        $this->assertTrue($this->isSuccessResponse($response), "La respuesta no indica success: true");
        $this->assertArrayHasKey('idMaquina', $response);
    }

    /** @test */
    public function sePuedenObtenerMaquinasPorEstado()
    {
        $response = $this->makeRequest('GET', '/maquina/estado/Ensamblandose');

        $this->assertEquals(200, $this->getLastHttpCode());
        $this->assertTrue($this->isSuccessResponse($response));
        $this->assertArrayHasKey('maquinas', $response);
        $this->assertIsArray($response['maquinas']);
    }

    /**
     * @test
     */
    public function sePuedenObtenerMaquinasPorEtapa()
    {
        $response = $this->makeRequest('GET', '/maquina/etapa/Montaje');

        $this->assertEquals(200, $this->getLastHttpCode(),
            'El endpoint /maquina/etapa/:etapa debe responder HTTP 200');

        $this->assertIsArray($response,
            'La respuesta debe ser un array JSON');
    }

    /** @test */
    public function sePuedenObtenerComponentes()
    {
        $response = $this->makeRequest('GET', '/componentes');

        $this->assertEquals(200, $this->getLastHttpCode());
        $this->assertTrue($this->isSuccessResponse($response));
        $this->assertArrayHasKey('componentes', $response);
        $this->assertArrayHasKey('total', $response);
    }

    /** @test */
    public function sePuedenObtenerComponentesDisponibles()
    {
        $response = $this->makeRequest('GET', '/componentes/disponibles');

        $this->assertEquals(200, $this->getLastHttpCode());
        $this->assertTrue($this->isSuccessResponse($response));
        $this->assertArrayHasKey('componentes', $response);
    }
}