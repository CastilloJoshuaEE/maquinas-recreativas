<?php
/**
 * Tests de repositorios
 * 
 * @package maquinas_recreativas\Tests\Infrastructure\Repositories
 */

namespace maquinas_recreativas\Tests\Infrastructure\Repositories;

use PHPUnit\Framework\TestCase;
use maquinas_recreativas\Tests\TestDatabase;
use maquinas_recreativas\Infrastructure\Repositories\PDOUsuarioRepository;
use maquinas_recreativas\Infrastructure\Repositories\PDOComercioRepository;
use maquinas_recreativas\Infrastructure\Repositories\PDOComponenteRepository;
use maquinas_recreativas\Domain\Usuario\Usuario;
use maquinas_recreativas\Domain\Usuario\TipoUsuario;
use maquinas_recreativas\Domain\Usuario\EstadoUsuario;
use maquinas_recreativas\Domain\Comercio\Comercio;
use maquinas_recreativas\Domain\Componente\Componente;
use maquinas_recreativas\Domain\Componente\TipoComponente;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Domain\Shared\ValueObjects\Email;
use maquinas_recreativas\Infrastructure\Security\CifradoHelper;

class RepositoryTest extends TestCase
{
    private TestDatabase $testDb;
    private PDOUsuarioRepository $usuarioRepository;
    private PDOComercioRepository $comercioRepository;
    private PDOComponenteRepository $componenteRepository;
    
    protected function setUp(): void
    {
        $this->testDb = TestDatabase::getInstance();
        $this->testDb->cleanDatabase();
        
        $this->usuarioRepository = new PDOUsuarioRepository($this->testDb);
        $this->comercioRepository = new PDOComercioRepository($this->testDb);
        $this->componenteRepository = new PDOComponenteRepository($this->testDb);
    }
    
    /**
     * @test
     * CP-071 - Guardar y encontrar usuario
     */
    public function testGuardarYEncontrarUsuario(): void
    {
        $id = Uuid::v4();
        $email = new Email('test@example.com');
        $emailEncriptado = CifradoHelper::encriptar($email->value());
        $ciEncriptada = CifradoHelper::encriptar('1234567890');
        
        $usuario = new Usuario(
            $id,
            'Juan',
            'Perez',
            $ciEncriptada,
            $email,
            'juanperez',
            password_hash('password123', PASSWORD_BCRYPT),
            new TipoUsuario('Usuario'),
            new EstadoUsuario('Activo')
        );
        
        $this->usuarioRepository->save($usuario);
        
        $encontrado = $this->usuarioRepository->findById($id);
        
        $this->assertNotNull($encontrado);
        $this->assertEquals('Juan', $encontrado->getNombre());
        $this->assertEquals('Perez', $encontrado->getApellido());
        $this->assertEquals('juanperez', $encontrado->getUsuarioAsignado());
    }
    
    /**
     * @test
     * CP-072 - Actualizar usuario
     */
    public function testActualizarUsuario(): void
    {
        $id = Uuid::v4();
        $email = new Email('original@example.com');
        $emailEncriptado = CifradoHelper::encriptar($email->value());
        $ciEncriptada = CifradoHelper::encriptar('1234567890');
        
        $usuario = new Usuario(
            $id,
            'Original',
            'Apellido',
            $ciEncriptada,
            $email,
            'originaluser',
            password_hash('password123', PASSWORD_BCRYPT),
            new TipoUsuario('Usuario'),
            new EstadoUsuario('Activo')
        );
        
        $this->usuarioRepository->save($usuario);
        
        $nuevoEmail = new Email('actualizado@example.com');
        $usuario->actualizarPerfil('Actualizado', 'NuevoApellido', $nuevoEmail, '0987654321', null);
        
        $this->usuarioRepository->save($usuario);
        
        $encontrado = $this->usuarioRepository->findById($id);
        
        $this->assertEquals('Actualizado', $encontrado->getNombre());
        $this->assertEquals('NuevoApellido', $encontrado->getApellido());
    }
    
    /**
     * @test
     * CP-073 - Eliminar usuario
     */
    public function testEliminarUsuario(): void
    {
        $id = Uuid::v4();
        $email = new Email('eliminar@example.com');
        $emailEncriptado = CifradoHelper::encriptar($email->value());
        $ciEncriptada = CifradoHelper::encriptar('9999999999');
        
        $usuario = new Usuario(
            $id,
            'Eliminar',
            'Test',
            $ciEncriptada,
            $email,
            'eliminartest',
            password_hash('password123', PASSWORD_BCRYPT),
            new TipoUsuario('Usuario'),
            new EstadoUsuario('Activo')
        );
        
        $this->usuarioRepository->save($usuario);
        
        $encontrado = $this->usuarioRepository->findById($id);
        $this->assertNotNull($encontrado);
        
        $this->usuarioRepository->delete($id);
        
        $eliminado = $this->usuarioRepository->findById($id);
        $this->assertNull($eliminado);
    }
    
    /**
     * @test
     * CP-074 - Guardar y encontrar comercio
     */
    public function testGuardarYEncontrarComercio(): void
    {
        $id = Uuid::v4();
        $comercio = Comercio::crear($id, 'Comercio Test', 'Minorista', 'Dirección 123', '0999999999');
        
        $this->comercioRepository->guardar($comercio);
        
        $encontrado = $this->comercioRepository->buscarPorId($id->value());
        
        $this->assertNotNull($encontrado);
        $this->assertEquals('Comercio Test', $encontrado->getNombre());
        $this->assertEquals('Minorista', $encontrado->getTipo());
    }
    
    /**
     * @test
     * CP-075 - Buscar comercio por nombre
     */
    public function testBuscarComercioPorNombre(): void
    {
        $id = Uuid::v4();
        $comercio = Comercio::crear($id, 'Comercio Buscar', 'Mayorista', 'Dirección', '0988888888');
        
        $this->comercioRepository->guardar($comercio);
        
        $encontrado = $this->comercioRepository->buscarPorNombre('Comercio Buscar');
        
        $this->assertNotNull($encontrado);
        $this->assertEquals($id->value(), $encontrado->getId());
    }
    
    /**
     * @test
     * CP-076 - Guardar y encontrar componente
     */
    public function testGuardarYEncontrarComponente(): void
    {
        $componente = Componente::crear(TipoComponente::ELECTRONICO(), 'Componente Test', 150.00);
        
        $this->componenteRepository->save($componente);
        
        $encontrado = $this->componenteRepository->findById($componente->id());
        
        $this->assertNotNull($encontrado);
        $this->assertEquals('Componente Test', $encontrado->nombre());
        $this->assertEquals(150.00, $encontrado->precio());
    }
    
    /**
     * @test
     * CP-077 - Buscar componentes por tipo
     */
    public function testBuscarComponentesPorTipo(): void
    {
        $componente1 = Componente::crear(TipoComponente::ELECTRONICO(), 'Placa Madre', 350.00);
        $componente2 = Componente::crear(TipoComponente::ELECTRONICO(), 'Memoria RAM', 120.00);
        $componente3 = Componente::crear(TipoComponente::ESTRUCTURAL(), 'Carcasa', 80.00);
        
        $this->componenteRepository->save($componente1);
        $this->componenteRepository->save($componente2);
        $this->componenteRepository->save($componente3);
        
        $electronicos = $this->componenteRepository->findByTipo(TipoComponente::ELECTRONICO());
        
        $this->assertCount(2, $electronicos);
        
        $estructurales = $this->componenteRepository->findByTipo(TipoComponente::ESTRUCTURAL());
        $this->assertCount(1, $estructurales);
    }
}