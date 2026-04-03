<?php
/**
 * Tests de queries de usuario
 */

namespace maquinas_recreativas\Tests\Application\Queries;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;

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
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;

class UsuarioQueryTest extends TestCase
{
    private TestDatabase $testDb;
    private MySQLUsuarioRepository $usuarioRepository;
    private BcryptPasswordHasher $passwordHasher;
    
    private function generarCiUnico(): string
    {
        return '1' . time() . rand(1000, 9999);
    }
    
    private function generarEmailUnico(string $base = 'test'): string
    {
        return $base . '_' . time() . '_' . rand(1000, 9999) . '@test.com';
    }
    
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
            ['Juan', 'Perez', $this->generarCiUnico(), $this->generarEmailUnico('juan'), 'password123', 'Administrador'],
            ['Maria', 'Gomez', $this->generarCiUnico(), $this->generarEmailUnico('maria'), 'password123', 'Usuario'],
            ['Carlos', 'Lopez', $this->generarCiUnico(), $this->generarEmailUnico('carlos'), 'password123', 'Tecnico', 'Ensamblador'],
            ['Ana', 'Martinez', $this->generarCiUnico(), $this->generarEmailUnico('ana'), 'password123', 'Tecnico', 'Comprobador'],
            ['Pedro', 'Rodriguez', $this->generarCiUnico(), $this->generarEmailUnico('pedro'), 'password123', 'Logistica']
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
     */
    public function testObtenerUsuarioPorId(): void
    {
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
     */
    public function testObtenerUsuarioInexistente(): void
    {
        $handler = new ObtenerUsuarioPorIdHandler($this->usuarioRepository);
        $query = new ObtenerUsuarioPorIdQuery(Uuid::v4(), true);
        
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Usuario no encontrado');
        
        $handler->handle($query);
    }
    
    /**
     * @test
     */
    public function testObtenerTodosUsuarios(): void
    {
        $handler = new ObtenerTodosUsuariosHandler($this->usuarioRepository);
        $query = new ObtenerTodosUsuariosQuery();
        $usuarios = $handler->handle($query);
        
        $this->assertIsArray($usuarios);
        // 5 usuarios creados + 1 admin del sistema = 6
        $this->assertCount(6, $usuarios);
    }
    
    /**
     * @test
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
     */
    public function testObtenerUsuariosPorTipoHandler(): void
    {
        $handler = new ObtenerUsuariosPorTipoHandler($this->usuarioRepository);
        
        $query = new ObtenerUsuariosPorTipoQuery('Administrador');
        $usuarios = $handler->handle($query);
        
        $this->assertIsArray($usuarios);
        // 1 admin creado + 1 admin del sistema = 2
        $this->assertCount(2, $usuarios);
    }
    
    /**
     * @test
     */
    public function testBuscarPorEmail(): void
    {
        $handler = new BuscarPorEmailHandler($this->usuarioRepository);
        
        // Obtener un email real de la BD
        $usuarios = $this->usuarioRepository->findAll([]);
        $emailBuscado = $usuarios[0]->getEmail()->value();
        
        $query = new BuscarPorEmailQuery($emailBuscado);
        $usuario = $handler->handle($query);
        
        $this->assertIsArray($usuario);
        $this->assertArrayHasKey('nombre', $usuario);
    }
    
    /**
     * @test
     */
    public function testBuscarPorEmailInexistente(): void
    {
        $handler = new BuscarPorEmailHandler($this->usuarioRepository);
        $query = new BuscarPorEmailQuery('inexistente_' . time() . '@test.com');
        
        $this->expectException(DomainException::class);
        
        $handler->handle($query);
    }
}