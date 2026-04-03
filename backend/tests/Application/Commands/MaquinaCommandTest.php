<?php
/**
 * Tests de comandos de máquina
 */

namespace maquinas_recreativas\Tests\Application\Commands;

use PHPUnit\Framework\TestCase;
use maquinas_recreativas\Tests\TestDatabase;
use maquinas_recreativas\Infrastructure\Repositories\MySQLUsuarioRepository;
use maquinas_recreativas\Infrastructure\Repositories\MySQLComercioRepository;
use maquinas_recreativas\Infrastructure\Repositories\MySQLMaquinaRepository;
use maquinas_recreativas\Infrastructure\Repositories\MySQLComponenteRepository;
use maquinas_recreativas\Infrastructure\Repositories\MySQLMontajeRepository;
use maquinas_recreativas\Infrastructure\Repositories\MySQLHistorialRepository;
use maquinas_recreativas\Infrastructure\Security\BcryptPasswordHasher;
use maquinas_recreativas\Application\Commands\Usuario\RegistrarUsuarioCommand;
use maquinas_recreativas\Application\Commands\Usuario\RegistrarUsuarioHandler;
use maquinas_recreativas\Application\Commands\Comercio\RegistrarComercioCommand;
use maquinas_recreativas\Application\Commands\Comercio\RegistrarComercioHandler;
use maquinas_recreativas\Application\Commands\Maquina\RegistrarMaquinaCommand;
use maquinas_recreativas\Application\Commands\Maquina\RegistrarMaquinaHandler;
use maquinas_recreativas\Application\Commands\Maquina\RegistrarMontajeCommand;
use maquinas_recreativas\Application\Commands\Maquina\RegistrarMontajeHandler;
use maquinas_recreativas\Application\Commands\Maquina\GenerarPlacaCommand;
use maquinas_recreativas\Application\Commands\Maquina\GenerarPlacaHandler;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;

class MaquinaCommandTest extends TestCase
{
    private TestDatabase $testDb;
    private MySQLUsuarioRepository $usuarioRepository;
    private MySQLComercioRepository $comercioRepository;
    private MySQLMaquinaRepository $maquinaRepository;
    private MySQLComponenteRepository $componenteRepository;
    private MySQLMontajeRepository $montajeRepository;
    private MySQLHistorialRepository $historialRepository;
    private BcryptPasswordHasher $passwordHasher;
    
    private Uuid $logisticaId;
    private Uuid $ensambladorId;
    private Uuid $comprobadorId;
    private string $comercioId;
    
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
        $this->comercioRepository = new MySQLComercioRepository($this->testDb);
        $this->maquinaRepository = new MySQLMaquinaRepository($this->testDb);
        $this->componenteRepository = new MySQLComponenteRepository($this->testDb);
        $this->montajeRepository = new MySQLMontajeRepository($this->testDb);
        $this->historialRepository = new MySQLHistorialRepository($this->testDb);
        $this->passwordHasher = new BcryptPasswordHasher();
        
        $this->crearDatosBase();
    }
    
    private function crearDatosBase(): void
    {
        $registrarUsuario = new RegistrarUsuarioHandler($this->usuarioRepository, $this->passwordHasher);
        
        // Usuario logística - usar CI único
        $logisticaCommand = new RegistrarUsuarioCommand(
            'Logistica', 'Test', $this->generarCiUnico(), $this->generarEmailUnico('logistica'), 'password123', 'Logistica'
        );
        $this->logisticaId = $registrarUsuario->handle($logisticaCommand);
        
        // Técnico ensamblador - usar CI único
        $ensambladorCommand = new RegistrarUsuarioCommand(
            'Ensamblador', 'Test', $this->generarCiUnico(), $this->generarEmailUnico('ensamblador'), 'password123', 'Tecnico', 'Ensamblador'
        );
        $this->ensambladorId = $registrarUsuario->handle($ensambladorCommand);
        
        // Técnico comprobador - usar CI único
        $comprobadorCommand = new RegistrarUsuarioCommand(
            'Comprobador', 'Test', $this->generarCiUnico(), $this->generarEmailUnico('comprobador'), 'password123', 'Tecnico', 'Comprobador'
        );
        $this->comprobadorId = $registrarUsuario->handle($comprobadorCommand);
        
        // Registrar comercio
        $registrarComercio = new RegistrarComercioHandler($this->comercioRepository, 
            \maquinas_recreativas\Infrastructure\Security\HistorialHelper::getInstance());
        $comercioCommand = new RegistrarComercioCommand(
            'Comercio Test ' . time(), 'Minorista', 'Dirección Test', '0999999999', $this->logisticaId->value()
        );
        $comercio = $registrarComercio->handle($comercioCommand);
        $this->comercioId = $comercio->getId();
    }
    
    /**
     * @test
     */
    public function testRegistrarMaquinaValida(): void
    {
        $registrarMaquina = new RegistrarMaquinaHandler(
            $this->maquinaRepository, $this->usuarioRepository, $this->comercioRepository, $this->componenteRepository
        );
        
        $placaId = Uuid::v4();
        $carcasaId = Uuid::v4();
        
        $maquinaCommand = new RegistrarMaquinaCommand(
            'Máquina Test', 'Tipo A', $this->comercioId, $this->logisticaId->value(), 
            $placaId->value(), $carcasaId->value()
        );
        
        $maquinaId = $registrarMaquina->handle($maquinaCommand);
        
        $this->assertNotEmpty($maquinaId);
        
        $maquina = $this->maquinaRepository->findById(new Uuid($maquinaId));
        $this->assertNotNull($maquina);
        $this->assertEquals('Máquina Test', $maquina->nombre());
    }
    
    /**
     * @test
     */
    public function testGenerarPlaca(): void
    {
        $generarPlaca = new GenerarPlacaHandler($this->componenteRepository, $this->usuarioRepository);
        
        $comando = new GenerarPlacaCommand($this->ensambladorId->value());
        $resultado = $generarPlaca->handle($comando);
        
        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('placa', $resultado);
        $this->assertArrayHasKey('idComponente', $resultado);
        $this->assertStringStartsWith('PL', $resultado['placa']);
    }
    
    /**
     * @test
     */
    public function testRegistrarMontaje(): void
    {
        // Primero crear componente
        $componente = \maquinas_recreativas\Domain\Componente\Componente::crear(
            \maquinas_recreativas\Domain\Componente\TipoComponente::ELECTRONICO(),
            'Componente Test',
            100.00
        );
        $this->componenteRepository->save($componente);
        
        // Registrar máquina
        $registrarMaquina = new RegistrarMaquinaHandler(
            $this->maquinaRepository, $this->usuarioRepository, $this->comercioRepository, $this->componenteRepository
        );
        
        $placaId = Uuid::v4();
        $carcasaId = Uuid::v4();
        
        $maquinaCommand = new RegistrarMaquinaCommand(
            'Máquina Montaje', 'Tipo A', $this->comercioId, $this->logisticaId->value(),
            $placaId->value(), $carcasaId->value()
        );
        $maquinaId = $registrarMaquina->handle($maquinaCommand);
        
        // Registrar montaje
        $registrarMontaje = new RegistrarMontajeHandler(
            $this->maquinaRepository, $this->componenteRepository, $this->montajeRepository,
            $this->historialRepository, $this->usuarioRepository
        );
        
        $montajeCommand = new RegistrarMontajeCommand(
            $maquinaId, $componente->id()->value(), $this->ensambladorId->value(), 'Montaje de prueba'
        );
        
        $registrarMontaje->handle($montajeCommand);
        
        // Verificar que el montaje se guardó
        $montajes = $this->montajeRepository->findByMaquina(new Uuid($maquinaId));
        $this->assertCount(1, $montajes);
        $this->assertEquals($componente->id(), $montajes[0]->idComponente());
    }
}