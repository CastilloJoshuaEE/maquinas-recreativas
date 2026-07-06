<?php
/**
 * Tests de comandos de comercio
 */

namespace maquinas_recreativas\Tests\Application\Commands;

use PHPUnit\Framework\TestCase;
use maquinas_recreativas\Tests\TestDatabase;
use maquinas_recreativas\Infrastructure\Repositories\PDOUsuarioRepository;
use maquinas_recreativas\Infrastructure\Repositories\PDOComercioRepository;
use maquinas_recreativas\Infrastructure\Security\BcryptPasswordHasher;
use maquinas_recreativas\Infrastructure\Security\HistorialHelper;
use maquinas_recreativas\Application\Commands\Usuario\RegistrarUsuarioCommand;
use maquinas_recreativas\Application\Commands\Usuario\RegistrarUsuarioHandler;
use maquinas_recreativas\Application\Commands\Usuario\CambiarEstadoUsuarioCommand;
use maquinas_recreativas\Application\Commands\Usuario\CambiarEstadoUsuarioHandler;
use maquinas_recreativas\Application\Commands\Comercio\RegistrarComercioCommand;
use maquinas_recreativas\Application\Commands\Comercio\RegistrarComercioHandler;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;

class ComercioCommandTest extends TestCase
{
    private TestDatabase $testDb;
    private PDOUsuarioRepository $usuarioRepository;
    private PDOComercioRepository $comercioRepository;
    private BcryptPasswordHasher $passwordHasher;
    private HistorialHelper $historialHelper;
    private Uuid $logisticaId;
    
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
        
        $this->usuarioRepository = new PDOUsuarioRepository($this->testDb);
        $this->comercioRepository = new PDOComercioRepository($this->testDb);
        $this->passwordHasher = new BcryptPasswordHasher();
        $this->historialHelper = HistorialHelper::getInstance();
        
        $this->crearUsuarioLogistica();
        $this->activarUsuarioLogistica();
    }
    
    private function crearUsuarioLogistica(): void
    {
        $registrarUsuario = new RegistrarUsuarioHandler($this->usuarioRepository, $this->passwordHasher);
        
        $logisticaCommand = new RegistrarUsuarioCommand(
            'Logistica', 'Test', 
            $this->generarCiUnico(), 
            $this->generarEmailUnico('logistica'), 
            'Logistica'
        );
        $this->logisticaId = $registrarUsuario->handle($logisticaCommand);
    }
    
    private function activarUsuarioLogistica(): void
    {
        $cambiarEstadoHandler = new CambiarEstadoUsuarioHandler($this->usuarioRepository);
        $cambiarEstadoHandler->handle(new CambiarEstadoUsuarioCommand($this->logisticaId, 'Activo'));
    }
    
    /**
     * @test
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
        
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Ya existe un comercio con ese nombre');
        
        $registrarComercio->handle($comando);
    }
    

}