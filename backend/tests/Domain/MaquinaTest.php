<?php
/**
 * Tests de la entidad MaquinaRecreativa
 * 
 * @package maquinas_recreativas\Tests\Domain
 */

namespace maquinas_recreativas\Tests\Domain;

use PHPUnit\Framework\TestCase;
use maquinas_recreativas\Domain\Maquina\MaquinaRecreativa;
use maquinas_recreativas\Domain\Maquina\EstadoMaquina;
use maquinas_recreativas\Domain\Maquina\EtapaMaquina;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;

class MaquinaTest extends TestCase
{
    private Uuid $idComercio;
    private Uuid $idEnsamblador;
    private Uuid $idComprobador;
    
    protected function setUp(): void
    {
        $this->idComercio = Uuid::v4();
        $this->idEnsamblador = Uuid::v4();
        $this->idComprobador = Uuid::v4();
    }
    
    /**
     * @test
     * CP-011 - Crear máquina recreativa válida
     */
    public function testCrearMaquinaValida(): void
    {
        $maquina = MaquinaRecreativa::crear(
            'Máquina Test',
            'Tipo A',
            $this->idComercio,
            $this->idEnsamblador,
            $this->idComprobador
        );
        
        $this->assertInstanceOf(MaquinaRecreativa::class, $maquina);
        $this->assertEquals('Máquina Test', $maquina->nombre());
        $this->assertEquals('Tipo A', $maquina->tipo());
        $this->assertEquals($this->idComercio, $maquina->idComercio());
        $this->assertEquals($this->idEnsamblador, $maquina->idTecnicoEnsamblador());
        $this->assertEquals($this->idComprobador, $maquina->idTecnicoComprobador());
        $this->assertTrue($maquina->estado()->equals(EstadoMaquina::ENSAMBLANDOSE()));
        $this->assertTrue($maquina->etapa()->equals(EtapaMaquina::MONTAJE()));
    }
    
    /**
     * @test
     * CP-012 - Enviar máquina a comprobación
     */
    public function testEnviarAComprobacion(): void
    {
        $maquina = $this->crearMaquinaBase();
        
        $maquina->enviarAComprobacion();
        
        $this->assertTrue($maquina->estado()->equals(EstadoMaquina::COMPROBANDOSE()));
        $this->assertTrue($maquina->etapa()->equals(EtapaMaquina::MONTAJE()));
    }
    
    /**
     * @test
     * CP-013 - Enviar máquina a reensamblar
     */
    public function testEnviarAReensamblar(): void
    {
        $maquina = $this->crearMaquinaBase();
        
        $maquina->enviarAReensamblar();
        
        $this->assertTrue($maquina->estado()->equals(EstadoMaquina::REENSAMBLANDOSE()));
        $this->assertTrue($maquina->etapa()->equals(EtapaMaquina::MONTAJE()));
    }
    
    /**
     * @test
     * CP-014 - Enviar máquina a distribución
     */
    public function testEnviarADistribucion(): void
    {
        $maquina = $this->crearMaquinaBase();
        
        $maquina->enviarADistribucion();
        
        $this->assertTrue($maquina->estado()->equals(EstadoMaquina::DISTRIBUYENDOSE()));
        $this->assertTrue($maquina->etapa()->equals(EtapaMaquina::DISTRIBUCION()));
    }
    
    /**
     * @test
     * CP-015 - Poner máquina operativa
     */
    public function testPonerOperativa(): void
    {
        $maquina = $this->crearMaquinaBase();
        
        $maquina->ponerOperativa();
        
        $this->assertTrue($maquina->estado()->equals(EstadoMaquina::OPERATIVA()));
        $this->assertTrue($maquina->etapa()->equals(EtapaMaquina::RECAUDACION()));
    }
    
    /**
     * @test
     * CP-016 - Solicitar mantenimiento
     */
    public function testSolicitarMantenimiento(): void
    {
        $maquina = $this->crearMaquinaBase();
        $idTecnicoMantenimiento = Uuid::v4();
        
        $maquina->solicitarMantenimiento($idTecnicoMantenimiento);
        
        $this->assertTrue($maquina->estado()->equals(EstadoMaquina::NO_OPERATIVA()));
        $this->assertTrue($maquina->etapa()->equals(EtapaMaquina::MONTAJE()));
        $this->assertEquals($idTecnicoMantenimiento, $maquina->idTecnicoMantenimiento());
    }
    
    /**
     * @test
     * CP-017 - Finalizar mantenimiento exitoso
     */
    public function testFinalizarMantenimientoExitoso(): void
    {
        $maquina = $this->crearMaquinaBase();
        $idTecnicoMantenimiento = Uuid::v4();
        
        $maquina->solicitarMantenimiento($idTecnicoMantenimiento);
        $maquina->finalizarMantenimiento(true);
        
        $this->assertTrue($maquina->estado()->equals(EstadoMaquina::OPERATIVA()));
        $this->assertTrue($maquina->etapa()->equals(EtapaMaquina::RECAUDACION()));
        $this->assertNull($maquina->idTecnicoMantenimiento());
    }
    
    /**
     * @test
     * CP-018 - Finalizar mantenimiento fallido (máquina retirada)
     */
    public function testFinalizarMantenimientoFallido(): void
    {
        $maquina = $this->crearMaquinaBase();
        $idTecnicoMantenimiento = Uuid::v4();
        
        $maquina->solicitarMantenimiento($idTecnicoMantenimiento);
        $maquina->finalizarMantenimiento(false);
        
        $this->assertTrue($maquina->estado()->equals(EstadoMaquina::RETIRADA()));
        $this->assertTrue($maquina->etapa()->equals(EtapaMaquina::DISTRIBUCION()));
        $this->assertNull($maquina->idTecnicoMantenimiento());
    }
    
    /**
     * @test
     * CP-019 - Transición de estado válida
     */
    public function testTransicionEstadoValida(): void
    {
        $maquina = $this->crearMaquinaBase();
        
        $maquina->enviarAComprobacion();
        $this->assertTrue($maquina->estado()->equals(EstadoMaquina::COMPROBANDOSE()));
        
        $maquina->enviarADistribucion();
        $this->assertTrue($maquina->estado()->equals(EstadoMaquina::DISTRIBUYENDOSE()));
        
        $maquina->ponerOperativa();
        $this->assertTrue($maquina->estado()->equals(EstadoMaquina::OPERATIVA()));
    }
    
    private function crearMaquinaBase(): MaquinaRecreativa
    {
        return MaquinaRecreativa::crear(
            'Máquina Test',
            'Tipo A',
            $this->idComercio,
            $this->idEnsamblador,
            $this->idComprobador
        );
    }
}