<?php
/**
 * Tests de la entidad Reporte
 * 
 * @package maquinas_recreativas\Tests\Domain
 */

namespace maquinas_recreativas\Tests\Domain;

use PHPUnit\Framework\TestCase;
use maquinas_recreativas\Domain\Reporte\Reporte;
use maquinas_recreativas\Domain\Reporte\EstadoReporte;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;

class ReporteTest extends TestCase
{
    private Uuid $emisorId;
    private Uuid $destinatarioId;
    
    protected function setUp(): void
    {
        $this->emisorId = Uuid::v4();
        $this->destinatarioId = Uuid::v4();
    }
    
    /**
     * @test
     * CP-050 - Crear reporte válido
     */
    public function testCrearReporteValido(): void
    {
        $reporte = Reporte::crear(
            $this->emisorId,
            $this->destinatarioId,
            'Problema con la máquina #123'
        );
        
        $this->assertInstanceOf(Reporte::class, $reporte);
        $this->assertEquals($this->emisorId, $reporte->idUsuarioEmisor());
        $this->assertEquals($this->destinatarioId, $reporte->idUsuarioDestinatario());
        $this->assertEquals('Problema con la máquina #123', $reporte->descripcion());
        $this->assertTrue($reporte->estado()->equals(EstadoReporte::PENDIENTE()));
        $this->assertTrue($reporte->esChat());
    }
    
    /**
     * @test
     * CP-051 - Crear reporte sin destinatario (sistema)
     */
    public function testCrearReporteSistema(): void
    {
        $reporte = Reporte::crear(
            $this->emisorId,
            null,
            'Reporte al sistema'
        );
        
        $this->assertNull($reporte->idUsuarioDestinatario());
        $this->assertFalse($reporte->esChat());
    }
    
    /**
     * @test
     * CP-052 - Actualizar estado de reporte
     */
    public function testActualizarEstado(): void
    {
        $reporte = Reporte::crear($this->emisorId, $this->destinatarioId, 'Reporte de prueba');
        
        $reporte->actualizarEstado(EstadoReporte::EN_PROCESO());
        $this->assertTrue($reporte->estado()->equals(EstadoReporte::EN_PROCESO()));
        
        $reporte->actualizarEstado(EstadoReporte::RESUELTO());
        $this->assertTrue($reporte->estado()->equals(EstadoReporte::RESUELTO()));
    }
    
    /**
     * @test
     * CP-053 - No se puede actualizar estado de reporte resuelto
     */
    public function testNoActualizarEstadoResuelto(): void
    {
        $reporte = Reporte::crear($this->emisorId, $this->destinatarioId, 'Reporte de prueba');
        
        $reporte->actualizarEstado(EstadoReporte::RESUELTO());
        
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('No se puede modificar un reporte ya resuelto');
        
        $reporte->actualizarEstado(EstadoReporte::EN_PROCESO());
    }
    
    /**
     * @test
     * CP-054 - Usuario tiene permiso para ver reporte (emisor)
     */
    public function testUsuarioTienePermisoEmisor(): void
    {
        $reporte = Reporte::crear($this->emisorId, $this->destinatarioId, 'Reporte de prueba');
        
        $this->assertTrue($reporte->usuarioTienePermiso($this->emisorId));
    }
    
    /**
     * @test
     * CP-055 - Usuario tiene permiso para ver reporte (destinatario)
     */
    public function testUsuarioTienePermisoDestinatario(): void
    {
        $reporte = Reporte::crear($this->emisorId, $this->destinatarioId, 'Reporte de prueba');
        
        $this->assertTrue($reporte->usuarioTienePermiso($this->destinatarioId));
    }
    
    /**
     * @test
     * CP-056 - Usuario no tiene permiso para ver reporte
     */
    public function testUsuarioNoTienePermiso(): void
    {
        $reporte = Reporte::crear($this->emisorId, $this->destinatarioId, 'Reporte de prueba');
        $usuarioExterno = Uuid::v4();
        
        $this->assertFalse($reporte->usuarioTienePermiso($usuarioExterno));
    }
}