<?php
/**
 * Tests de comandos de comercio
 * 
 * @package maquinas_recreativas\Tests\Application\Commands
 */

namespace maquinas_recreativas\Tests\Application\Commands;

use PHPUnit\Framework\TestCase;
use maquinas_recreativas\Tests\TestDatabase;
use maquinas_recreativas\Infrastructure\Repositories\MySQLUsuarioRepository;
use maquinas_recreativas\Infrastructure\Repositories\MySQLComercioRepository;
use maquinas_recreativas\Infrastructure\Security\BcryptPasswordHasher;
use maquinas_recreativas\Infrastructure\Security\HistorialHelper;
use maquinas_recreativas\Application\Commands\Usuario\RegistrarUsuarioCommand;
use maquinas_recreativas\Application\Commands\Usuario\RegistrarUsuarioHandler;
use maquinas_recreativas\Application\Commands\Comercio\RegistrarComercioCommand;
use maquinas_recreativas\Application\Commands\Comercio\RegistrarComercioHandler;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;

class ComercioCommandTest extends TestCase
{
    private TestDatabase $testDb;
    private MySQLUsuarioRepository $usuarioRepository;
    private MySQLComercioRepository $comercioRepository;
    private BcryptPasswordHasher $passwordHasher;
    private HistorialHelper $historialHelper;
    private Uuid $logisticaId;
    
    protected function setUp(): void
    {
        $this->testDb = TestDatabase::getInstance();
        $this->testDb->cleanDatabase();
        
        $this->usuarioRepository = new MySQLUsuarioRepository($this->testDb);
        $this->comercioRepository = new MySQLComercioRepository($this->testDb);
        $this->passwordHasher = new BcryptPasswordHasher();
        $this->historialHelper = HistorialHelper::getInstance();
        
        $this->crearUsuarioLogistica();
    }
    
    private function crearUsuarioLogistica(): void
    {
        $registrarUsuario = new RegistrarUsuarioHandler($this->usuarioRepository, $this->passwordHasher);
        
        $logisticaCommand = new RegistrarUsuarioCommand(
            'Logistica', 'Test', '1111111111', 'logistica@test.com', 'password123', 'Logistica'
        );
        $this->logisticaId = $registrarUsuario->handle($logisticaCommand);
    }
    
    /**
     * @test
     * CP-064 - Registrar comercio válido
     */
    public function testRegistrarComercioValido(): void
    {
        $registrarComercio = new RegistrarComercioHandler($this->comercioRepository, $this->historialHelper);
        
        $comando = new RegistrarComercioCommand(
            'Comercio Test',
            'Minorista',
            'Av. Principal 123',
            '0999999999',
            $this->logisticaId->value()
        );
        
        $comercio = $registrarComercio->handle($comando);
        
        $this->assertNotNull($comercio);
        $this->assertEquals('Comercio Test', $comercio->getNombre());
        $this->assertEquals('Minorista', $comercio->getTipo());
        $this->assertEquals('Av. Principal 123', $comercio->getDireccion());
        $this->assertEquals('0999999999', $comercio->getTelefono());
    }
    
    /**
     * @test
     * CP-065 - Registrar comercio con nombre duplicado
     */
    public function testRegistrarComercioDuplicado(): void
    {
        $registrarComercio = new RegistrarComercioHandler($this->comercioRepository, $this->historialHelper);
        
        $comando = new RegistrarComercioCommand(
            'Comercio Duplicado',
            'Mayorista',
            'Dirección 123',
            '0988888888',
            $this->logisticaId->value()
        );
        
        $registrarComercio->handle($comando);
        
        $this->expectException(\maquinas_recreativas\Domain\Shared\Exceptions\DomainException::class);
        $this->expectExceptionMessage('Ya existe un comercio con ese nombre');
        
        $registrarComercio->handle($comando);
    }
    
    /**
     * @test
     * CP-066 - Registrar comercio con tipo inválido
     */
    public function testRegistrarComercioTipoInvalido(): void
    {
        // Este test se maneja en el controlador, pero podemos probar la entidad
        $comando = new RegistrarComercioCommand(
            'Comercio Invalido',
            'TipoInvalido',
            'Dirección',
            '0977777777',
            $this->logisticaId->value()
        );
        
        $registrarComercio = new RegistrarComercioHandler($this->comercioRepository, $this->historialHelper);
        
        // La validación de tipo debería estar en el handler o en la entidad
        // Si no está, este test fallará como advertencia
        try {
            $registrarComercio->handle($comando);
            $this->fail('Debería haber lanzado una excepción por tipo inválido');
        } catch (\Exception $e) {
            $this->assertInstanceOf(\maquinas_recreativas\Domain\Shared\Exceptions\DomainException::class, $e);
        }
    }
}