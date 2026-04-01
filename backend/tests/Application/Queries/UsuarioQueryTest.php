<?php
/**
 * Tests de queries de usuario
 * 
 * @package maquinas_recreativas\Tests\Application\Queries
 */

namespace maquinas_recreativas\Tests\Application\Queries;

use PHPUnit\Framework\TestCase;
use maquinas_recreativas\Tests\TestDatabase;
use maquinas_recreativas\Infrastructure\Repositories\MySQLUsuarioRepository;
use maquinas_recreativas\Infrastructure\Security\BcryptPasswordHasher;
use maquinas_recreativas\Application\Commands\Usuario\RegistrarUsuarioCommand;
use maquinas_recreativas\Application\Commands\Usuario\RegistrarUsuarioHandler;
use maquinas_recreativas\Application\Queries\Usuario\ObtenerUsuarioPorIdQuery;
use maquinas_recreativas\Application\Queries\Usuario\ObtenerUsuarioPorIdHandler;
use maquinas_recreativas\Application\Queries\Usuario\ObtenerTodosUsuariosQuery;
use maquinas_recreativas\Application\Queries\Usuario\ObtenerTodosUsuariosHandler;
use maquinas_recreativas\Application\Queries\Usuario\ObtenerTecnicosPorEspecialidadQuery;
use maquinas_recreativas\Application\Queries\Usuario\ObtenerTecnicosPorEspecialidadHandler;
use maquinas_recreativas\Application\Queries\Usuario\ObtenerUsuariosPorTipoQuery;
use maquinas_recreativas\Application\Queries\Usuario\ObtenerUsuariosPorTipoHandler;
use maquinas_recreativas\Application\Queries\Usuario\BuscarPorEmailQuery;
use maquinas_recreativas\Application\Queries\Usuario\BuscarPorEmailHandler;

class UsuarioQueryTest extends TestCase
{
    private TestDatabase $testDb;
    private MySQLUsuarioRepository $usuarioRepository;
    private BcryptPasswordHasher $passwordHasher;
    
    protected function setUp(): void
    {
        $this->testDb = TestDatabase::getInstance();
        $this->testDb->cleanDatabase();
        
        $this->usuarioRepository = new MySQLUsuarioRepository($this->testDb);
        $this->passwordHasher = new BcryptPasswordHasher();
        
        $this->crearUsuariosDePrueba();
    }
    
    private function crearUsuariosDePrueba(): void
    {
        $registrarHandler = new RegistrarUsuarioHandler($this->usuarioRepository, $this->passwordHasher);
        
        $usuarios = [
            ['Juan', 'Perez', '1111111111', 'juan@test.com', 'password123', 'Administrador'],
            ['Maria', 'Gomez', '2222222222', 'maria@test.com', 'password123', 'Usuario'],
            ['Carlos', 'Lopez', '3333333333', 'carlos@test.com', 'password123', 'Tecnico', 'Ensamblador'],
            ['Ana', 'Martinez', '4444444444', 'ana@test.com', 'password123', 'Tecnico', 'Comprobador'],
            ['Pedro', 'Rodriguez', '5555555555', 'pedro@test.com', 'password123', 'Logistica']
        ];
        
        foreach ($usuarios as $usuario) {
            $command = new RegistrarUsuarioCommand(
                $usuario[0],
                $usuario[1],
                $usuario[2],
                $usuario[3],
                $usuario[4],
                $usuario[5],
                $usuario[6] ?? null
            );
            $registrarHandler->handle($command);
        }
    }
    
    /**
     * @test
     * CP-038 - Obtener usuario por ID
     */
    public function testObtenerUsuarioPorId(): void
    {
        // Obtener un usuario existente
        $usuarios = $this->usuarioRepository->findAll([]);
        $primerUsuario = $usuarios[0];
        
        $handler = new ObtenerUsuarioPorIdHandler($this->usuarioRepository);
        
        $query = new ObtenerUsuarioPorIdQuery($primerUsuario->getId(), true);
        $resultado = $handler->handle($query);
        
        $this->assertIsArray($resultado);
        $this->assertEquals($primerUsuario->getId()->value(), $resultado['id']);
        $this->assertEquals($primerUsuario->getNombre(), $resultado['nombre']);
    }
    
    /**
     * @test
     * CP-039 - Obtener usuario inexistente
     */
    public function testObtenerUsuarioInexistente(): void
    {
        $handler = new ObtenerUsuarioPorIdHandler($this->usuarioRepository);
        
        $query = new ObtenerUsuarioPorIdQuery(\maquinas_recreativas\Domain\Shared\ValueObjects\Uuid::v4(), true);
        
        $this->expectException(\maquinas_recreativas\Domain\Shared\Exceptions\DomainException::class);
        $this->expectExceptionMessage('Usuario no encontrado');
        
        $handler->handle($query);
    }
    
    /**
     * @test
     * CP-040 - Obtener todos los usuarios
     */
    public function testObtenerTodosUsuarios(): void
    {
        $handler = new ObtenerTodosUsuariosHandler($this->usuarioRepository);
        
        $query = new ObtenerTodosUsuariosQuery();
        $usuarios = $handler->handle($query);
        
        $this->assertIsArray($usuarios);
        $this->assertCount(5, $usuarios);
    }
    
    /**
     * @test
     * CP-041 - Obtener usuarios filtrados por tipo
     */
    public function testObtenerUsuariosPorTipo(): void
    {
        $handler = new ObtenerTodosUsuariosHandler($this->usuarioRepository);
        
        $query = new ObtenerTodosUsuariosQuery('Tecnico');
        $usuarios = $handler->handle($query);
        
        $this->assertIsArray($usuarios);
        $this->assertCount(2, $usuarios);
        
        foreach ($usuarios as $usuario) {
            $this->assertEquals('Tecnico', $usuario['tipo']);
        }
    }
    
    /**
     * @test
     * CP-042 - Obtener técnicos por especialidad
     */
    public function testObtenerTecnicosPorEspecialidad(): void
    {
        $handler = new ObtenerTecnicosPorEspecialidadHandler($this->usuarioRepository);
        
        $query = new ObtenerTecnicosPorEspecialidadQuery('Ensamblador');
        $tecnicos = $handler->handle($query);
        
        $this->assertIsArray($tecnicos);
        $this->assertCount(1, $tecnicos);
        $this->assertEquals('Carlos', $tecnicos[0]['nombre']);
        $this->assertEquals('Ensamblador', $tecnicos[0]['especialidad']);
    }
    
    /**
     * @test
     * CP-043 - Obtener técnicos por especialidad vacía
     */
    public function testObtenerTecnicosPorEspecialidadVacia(): void
    {
        $handler = new ObtenerTecnicosPorEspecialidadHandler($this->usuarioRepository);
        
        $query = new ObtenerTecnicosPorEspecialidadQuery('Mantenimiento');
        $tecnicos = $handler->handle($query);
        
        $this->assertIsArray($tecnicos);
        $this->assertCount(0, $tecnicos);
    }
    
    /**
     * @test
     * CP-044 - Obtener usuarios por tipo con query específica
     */
    public function testObtenerUsuariosPorTipoHandler(): void
    {
        $handler = new ObtenerUsuariosPorTipoHandler($this->usuarioRepository);
        
        $query = new ObtenerUsuariosPorTipoQuery('Administrador');
        $usuarios = $handler->handle($query);
        
        $this->assertIsArray($usuarios);
        $this->assertCount(1, $usuarios);
        $this->assertEquals('Juan', $usuarios[0]['nombre']);
    }
    
    /**
     * @test
     * CP-045 - Buscar usuario por email
     */
    public function testBuscarPorEmail(): void
    {
        $handler = new BuscarPorEmailHandler($this->usuarioRepository);
        
        $query = new BuscarPorEmailQuery('juan@test.com');
        $usuario = $handler->handle($query);
        
        $this->assertIsArray($usuario);
        $this->assertEquals('Juan', $usuario['nombre']);
        $this->assertEquals('Perez', $usuario['apellido']);
    }
    
    /**
     * @test
     * CP-046 - Buscar usuario por email inexistente
     */
    public function testBuscarPorEmailInexistente(): void
    {
        $handler = new BuscarPorEmailHandler($this->usuarioRepository);
        
        $query = new BuscarPorEmailQuery('inexistente@test.com');
        
        $this->expectException(\maquinas_recreativas\Domain\Shared\Exceptions\DomainException::class);
        
        $handler->handle($query);
    }
}