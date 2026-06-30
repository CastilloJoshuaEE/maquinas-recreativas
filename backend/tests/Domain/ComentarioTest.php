<?php
/**
 * Tests de la entidad Comentario
 * 
 * @package maquinas_recreativas\Tests\Domain
 */

namespace maquinas_recreativas\Tests\Domain;

use PHPUnit\Framework\TestCase;
use maquinas_recreativas\Domain\Comentario\Comentario;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;

class ComentarioTest extends TestCase
{
    private Uuid $reporteId;
    private Uuid $usuarioId;
    
    protected function setUp(): void
    {
        $this->reporteId = Uuid::v4();
        $this->usuarioId = Uuid::v4();
    }
    
    /**
     * @test
     * CP-057 - Crear comentario válido
     */
    public function testCrearComentarioValido(): void
    {
        $comentario = Comentario::crear(
            $this->reporteId,
            $this->usuarioId,
            'Este es un comentario de prueba'
        );
        
        $this->assertInstanceOf(Comentario::class, $comentario);
        $this->assertEquals($this->reporteId, $comentario->idReporte());
        $this->assertEquals($this->usuarioId, $comentario->idUsuarioEmisor());
        $this->assertEquals('Este es un comentario de prueba', $comentario->comentario());
        $this->assertNotNull($comentario->fechaHora());
    }
    
    /**
     * @test
     * CP-058 - No se puede crear comentario vacío
     */
    public function testNoCrearComentarioVacio(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('El comentario no puede estar vacío');
        
        Comentario::crear($this->reporteId, $this->usuarioId, '');
    }
    
    /**
     * @test
     * CP-059 - No se puede crear comentario con solo espacios
     */
    public function testNoCrearComentarioSoloEspacios(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('El comentario no puede estar vacío');
        
        Comentario::crear($this->reporteId, $this->usuarioId, '   ');
    }
    
    /**
     * @test
     * CP-060 - Verificar si comentario es de usuario
     */
    public function testEsDeUsuario(): void
    {
        $comentario = Comentario::crear($this->reporteId, $this->usuarioId, 'Comentario');
        
        $this->assertTrue($comentario->esDeUsuario($this->usuarioId));
        
        $otroUsuario = Uuid::v4();
        $this->assertFalse($comentario->esDeUsuario($otroUsuario));
    }
}