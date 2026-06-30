<?php
/**
 * Tests de la entidad Usuario
 * 
 * @package maquinas_recreativas\Tests\Domain
 */

namespace maquinas_recreativas\Tests\Domain;

use PHPUnit\Framework\TestCase;
use maquinas_recreativas\Domain\Usuario\Usuario;
use maquinas_recreativas\Domain\Usuario\Tecnico;
use maquinas_recreativas\Domain\Usuario\Logistica;
use maquinas_recreativas\Domain\Usuario\TipoUsuario;
use maquinas_recreativas\Domain\Usuario\EstadoUsuario;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Domain\Shared\ValueObjects\Email;

class UsuarioTest extends TestCase
{
    private Uuid $id;
    private Email $email;
    
    protected function setUp(): void
    {
        $this->id = Uuid::v4();
        $this->email = new Email('test@example.com');
    }
    
    /**
     * @test
     * CP-001 - Crear usuario administrador válido
     */
    public function testCrearUsuarioAdministrador(): void
    {
        $usuario = new Usuario(
            $this->id,
            'Juan',
            'Perez',
            '1234567890',
            $this->email,
            'juanperez',
            password_hash('password123', PASSWORD_BCRYPT),
            new TipoUsuario('Administrador'),
            new EstadoUsuario('Activo')
        );
        
        $this->assertEquals('Juan', $usuario->getNombre());
        $this->assertEquals('Perez', $usuario->getApellido());
        $this->assertEquals('juanperez', $usuario->getUsuarioAsignado());
        $this->assertTrue($usuario->estaActivo());
        $this->assertFalse($usuario->esTecnico());
    }
    
    /**
     * @test
     * CP-002 - Crear usuario técnico con especialidad
     */
    public function testCrearUsuarioTecnico(): void
    {
        $tecnico = new Tecnico(
            $this->id,
            'Carlos',
            'Lopez',
            '0987654321',
            $this->email,
            'carloslopez',
            password_hash('password123', PASSWORD_BCRYPT),
            new EstadoUsuario('Activo'),
            'Ensamblador'
        );
        
        $this->assertEquals('Ensamblador', $tecnico->getEspecialidad());
        $this->assertTrue($tecnico->esTecnico());
        $this->assertTrue($tecnico->esEnsamblador());
        $this->assertFalse($tecnico->esComprobador());
        $this->assertEquals(0, $tecnico->getCantidadActividades());
        
        $tecnico->incrementarActividades();
        $this->assertEquals(1, $tecnico->getCantidadActividades());
    }
    
    /**
     * @test
     * CP-003 - Crear usuario logística
     */
    public function testCrearUsuarioLogistica(): void
    {
        $logistica = new Logistica(
            $this->id,
            'Maria',
            'Gonzalez',
            '1122334455',
            $this->email,
            'mariagonzalez',
            password_hash('password123', PASSWORD_BCRYPT),
            new EstadoUsuario('Activo')
        );
        
        $this->assertEquals('Maria', $logistica->getNombre());
        $this->assertTrue($logistica->getTipo()->isLogistica());
    }
    
    /**
     * @test
     * CP-004 - Validar que el nombre no puede estar vacío
     */
    public function testNombreNoPuedeEstarVacio(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('El nombre no puede estar vacío');
        
        new Usuario(
            $this->id,
            '',
            'Perez',
            '1234567890',
            $this->email,
            'juanperez',
            password_hash('password123', PASSWORD_BCRYPT),
            new TipoUsuario('Usuario'),
            new EstadoUsuario('Activo')
        );
    }
    
    /**
     * @test
     * CP-005 - Validar que el apellido no puede estar vacío
     */
    public function testApellidoNoPuedeEstarVacio(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('El apellido no puede estar vacío');
        
        new Usuario(
            $this->id,
            'Juan',
            '',
            '1234567890',
            $this->email,
            'juanperez',
            password_hash('password123', PASSWORD_BCRYPT),
            new TipoUsuario('Usuario'),
            new EstadoUsuario('Activo')
        );
    }
    
    /**
     * @test
     * CP-006 - Validar cédula con longitud mínima
     */
    public function testCiDebeTenerAlMenos6Caracteres(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La cédula debe tener al menos 6 caracteres');
        
        new Usuario(
            $this->id,
            'Juan',
            'Perez',
            '123',
            $this->email,
            'juanperez',
            password_hash('password123', PASSWORD_BCRYPT),
            new TipoUsuario('Usuario'),
            new EstadoUsuario('Activo')
        );
    }
    
    /**
     * @test
     * CP-007 - Validar nombre de usuario con longitud mínima
     */
    public function testUsuarioAsignadoDebeTenerAlMenos3Caracteres(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('El nombre de usuario debe tener al menos 3 caracteres');
        
        new Usuario(
            $this->id,
            'Juan',
            'Perez',
            '1234567890',
            $this->email,
            'ju',
            password_hash('password123', PASSWORD_BCRYPT),
            new TipoUsuario('Usuario'),
            new EstadoUsuario('Activo')
        );
    }
    
    /**
     * @test
     * CP-008 - Actualizar perfil de usuario
     */
    public function testActualizarPerfil(): void
    {
        $usuario = new Usuario(
            $this->id,
            'Juan',
            'Perez',
            '1234567890',
            $this->email,
            'juanperez',
            password_hash('oldpassword', PASSWORD_BCRYPT),
            new TipoUsuario('Usuario'),
            new EstadoUsuario('Activo')
        );
        
        $nuevoEmail = new Email('juan.nuevo@example.com');
        $nuevoHash = password_hash('newpassword', PASSWORD_BCRYPT);
        
        $usuario->actualizarPerfil('Juan Carlos', 'Perez Gomez', $nuevoEmail, '0987654321', $nuevoHash);
        
        $this->assertEquals('Juan Carlos', $usuario->getNombre());
        $this->assertEquals('Perez Gomez', $usuario->getApellido());
        $this->assertEquals('0987654321', $usuario->getCi());
        $this->assertEquals($nuevoHash, $usuario->getContrasenaHash());
    }
    
    /**
     * @test
     * CP-009 - Cambiar estado de usuario
     */
    public function testCambiarEstado(): void
    {
        $usuario = new Usuario(
            $this->id,
            'Juan',
            'Perez',
            '1234567890',
            $this->email,
            'juanperez',
            password_hash('password123', PASSWORD_BCRYPT),
            new TipoUsuario('Usuario'),
            new EstadoUsuario('Activo')
        );
        
        $this->assertTrue($usuario->estaActivo());
        
        $usuario->desactivar();
        $this->assertFalse($usuario->estaActivo());
        
        $usuario->activar();
        $this->assertTrue($usuario->estaActivo());
    }
    
    /**
     * @test
     * CP-010 - Técnico con especialidad inválida
     */
    public function testTecnicoEspecialidadInvalida(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        
        new Tecnico(
            $this->id,
            'Carlos',
            'Lopez',
            '0987654321',
            $this->email,
            'carloslopez',
            password_hash('password123', PASSWORD_BCRYPT),
            new EstadoUsuario('Activo'),
            'EspecialidadInvalida'
        );
    }
}