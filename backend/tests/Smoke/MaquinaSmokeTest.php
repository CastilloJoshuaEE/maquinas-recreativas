<?php
// tests/Smoke/MaquinaSmokeTest.php

require_once __DIR__ . '/SmokeTestCase.php';

class MaquinaSmokeTest extends SmokeTestCase
{
    private ?string $comercioId = null;
    private ?string $placaId = null;
    private ?string $carcasaId = null;
    private ?string $maquinaId = null;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Login como administrador para crear recursos
        $usuario = $this->loginAsTestUser();
        
        // Crear comercio de prueba
        $comercioData = [
            'nombre' => 'Comercio Smoke Test ' . time(),
            'tipo' => 'Minorista',
            'direccion' => 'Calle Smoke 123',
            'telefono' => '0999' . rand(100000, 999999)
        ];
        
        $response = $this->makeRequest('POST', '/comercio/register', $comercioData);
        if ($this->isSuccessResponse($response)) {
            $this->comercioId = $response['idComercio'] ?? null;
        }
    }

    /**
     * @test
     */
    public function sePuedeGenerarUnaPlaca()
    {
        $response = $this->makeRequest('POST', '/maquina/generar-placa', []);
        
        $this->assertEquals(200, $this->getLastHttpCode());
        $this->assertTrue($this->isSuccessResponse($response));
        $this->assertArrayHasKey('placa', $response);
        $this->assertArrayHasKey('idComponente', $response);
        
        $this->placaId = $response['idComponente'];
    }

    /**
     * @test
     * @depends sePuedeGenerarUnaPlaca
     */
    public function sePuedeRegistrarUnaMaquina()
    {
        $this->assertNotNull($this->comercioId, 'Comercio no creado');
        $this->assertNotNull($this->placaId, 'Placa no generada');
        
        // Generar carcasa dentro del mismo test
        $response = $this->makeRequest('POST', '/maquina/generar-placa', []);
        if ($this->isSuccessResponse($response)) {
            $this->carcasaId = $response['idComponente'];
        }
        $this->assertNotNull($this->carcasaId, 'Carcasa no generada');
        
        $maquinaData = [
            'nombre' => 'Maquina Smoke ' . time(),
            'tipo' => 'Arcade',
            'idComercio' => $this->comercioId,
            'idPlaca' => $this->placaId,
            'idCarcasa' => $this->carcasaId
        ];
        
        $response = $this->makeRequest('POST', '/maquina/register', $maquinaData);
        
        $this->assertEquals(201, $this->getLastHttpCode());
        $this->assertTrue($this->isSuccessResponse($response));
        $this->assertArrayHasKey('idMaquina', $response);
        
        $this->maquinaId = $response['idMaquina'];
    }

    /**
     * @test
     */
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
        
        $this->assertEquals(200, $this->getLastHttpCode());
        $this->assertTrue($this->isSuccessResponse($response));
        $this->assertArrayHasKey('maquinas', $response);
    }

    /**
     * @test
     */
    public function sePuedenObtenerComponentes()
    {
        $response = $this->makeRequest('GET', '/componentes');
        
        $this->assertEquals(200, $this->getLastHttpCode());
        $this->assertTrue($this->isSuccessResponse($response));
        $this->assertArrayHasKey('componentes', $response);
        $this->assertArrayHasKey('total', $response);
    }

    /**
     * @test
     */
    public function sePuedenObtenerComponentesDisponibles()
    {
        $response = $this->makeRequest('GET', '/componentes/disponibles');
        
        $this->assertEquals(200, $this->getLastHttpCode());
        $this->assertTrue($this->isSuccessResponse($response));
        $this->assertArrayHasKey('componentes', $response);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}