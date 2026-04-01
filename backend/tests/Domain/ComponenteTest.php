<?php
/**
 * Tests de la entidad Componente
 * 
 * @package maquinas_recreativas\Tests\Domain
 */

namespace maquinas_recreativas\Tests\Domain;

use PHPUnit\Framework\TestCase;
use maquinas_recreativas\Domain\Componente\Componente;
use maquinas_recreativas\Domain\Componente\TipoComponente;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;

class ComponenteTest extends TestCase
{
    /**
     * @test
     * CP-020 - Crear componente válido
     */
    public function testCrearComponente(): void
    {
        $componente = Componente::crear(
            TipoComponente::ELECTRONICO(),
            'Placa Madre',
            350.00
        );
        
        $this->assertInstanceOf(Componente::class, $componente);
        $this->assertEquals('Placa Madre', $componente->nombre());
        $this->assertEquals(350.00, $componente->precio());
        $this->assertTrue($componente->estaDisponible());
        $this->assertFalse($componente->estaAsignado());
    }
    
    /**
     * @test
     * CP-021 - Generar placa con nomenclatura
     */
    public function testGenerarPlaca(): void
    {
        $numeroPlaca = 'PL24001';
        $placa = Componente::generarPlaca($numeroPlaca);
        
        $this->assertEquals($numeroPlaca, $placa->nombre());
        $this->assertTrue($placa->tipo()->equals(TipoComponente::LOGISTICO()));
        $this->assertEquals(120.00, $placa->precio());
    }
    
    /**
     * @test
     * CP-022 - Asignar componente a usuario
     */
    public function testAsignarComponente(): void
    {
        $componente = Componente::crear(
            TipoComponente::ESTRUCTURAL(),
            'Carcasa',
            150.00
        );
        
        $idUsuario = Uuid::v4();
        $componente->asignarAUso($idUsuario);
        
        $this->assertFalse($componente->estaDisponible());
        $this->assertTrue($componente->estaAsignado());
        $this->assertEquals($idUsuario, $componente->usuarioAsignado());
        $this->assertNotNull($componente->fechaAsignacion());
    }
    
    /**
     * @test
     * CP-023 - Asignar componente a usuario con máquina
     */
    public function testAsignarComponenteConMaquina(): void
    {
        $componente = Componente::crear(
            TipoComponente::ELECTRONICO(),
            'Pantalla',
            200.00
        );
        
        $idUsuario = Uuid::v4();
        $idMaquina = Uuid::v4();
        $componente->asignarAUso($idUsuario, $idMaquina);
        
        $this->assertEquals($idUsuario, $componente->usuarioAsignado());
        $this->assertEquals($idMaquina, $componente->maquinaAsignada());
    }
    
    /**
     * @test
     * CP-024 - No se puede asignar componente ya asignado
     */
    public function testNoSePuedeAsignarComponenteYaAsignado(): void
    {
        $componente = Componente::crear(
            TipoComponente::ELECTRONICO(),
            'Procesador',
            300.00
        );
        
        $idUsuario1 = Uuid::v4();
        $componente->asignarAUso($idUsuario1);
        
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('El componente no está disponible para asignación');
        
        $idUsuario2 = Uuid::v4();
        $componente->asignarAUso($idUsuario2);
    }
    
    /**
     * @test
     * CP-025 - Liberar componente asignado
     */
    public function testLiberarComponente(): void
    {
        $componente = Componente::crear(
            TipoComponente::ESTRUCTURAL(),
            'Soporte',
            80.00
        );
        
        $idUsuario = Uuid::v4();
        $componente->asignarAUso($idUsuario);
        
        // Verificar que está asignado
        $this->assertTrue($componente->estaAsignado());
        $this->assertFalse($componente->estaDisponible());
        
        // Liberar componente
        $componente->liberar();
        
        // Después de liberar, ya no está asignado (fechaLiberacion no es null)
        $this->assertFalse($componente->estaAsignado());
        // Pero usuarioAsignado aún existe, por lo que no está disponible
        $this->assertFalse($componente->estaDisponible());
        $this->assertNotNull($componente->fechaLiberacion());
        $this->assertNotNull($componente->usuarioAsignado());
    }
    
    /**
     * @test
     * CP-026 - No se puede liberar componente disponible
     */
    public function testNoSePuedeLiberarComponenteDisponible(): void
    {
        $componente = Componente::crear(
            TipoComponente::ELECTRONICO(),
            'Memoria RAM',
            120.00
        );
        
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('El componente ya está disponible');
        
        $componente->liberar();
    }
    
    /**
     * @test
     * CP-027 - Tipo componente válido
     */
    public function testTipoComponenteValido(): void
    {
        $logistico = TipoComponente::LOGISTICO();
        $electronico = TipoComponente::ELECTRONICO();
        $estructural = TipoComponente::ESTRUCTURAL();
        $accesorio = TipoComponente::ACCESORIO();
        
        $this->assertEquals('Logistico', $logistico->value());
        $this->assertEquals('Electronico', $electronico->value());
        $this->assertEquals('Estructural', $estructural->value());
        $this->assertEquals('Accesorio', $accesorio->value());
    }
    
    /**
     * @test
     * CP-028 - Tipo componente inválido
     */
    public function testTipoComponenteInvalido(): void
    {
        $this->expectException(\maquinas_recreativas\Domain\Shared\Exceptions\DomainException::class);
        
        TipoComponente::fromString('TipoInvalido');
    }
}