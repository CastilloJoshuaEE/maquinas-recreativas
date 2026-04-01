<?php
/**
 * Tests de comandos de máquina
 * 
 * @package maquinas_recreativas\Tests\Application\Commands
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
use maquinas_recreativas\Infrastructure\Repositories\MySQLNotificacionRepository;
use maquinas_recreativas\Infrastructure\Repositories\MySQLDistribucionRepository;
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
        
        // Usuario logística
        $logisticaCommand = new RegistrarUsuarioCommand(
            'Logistica', 'Test', '1111111111', 'logistica@test.com', 'password123', 'Logistica'
        );
        $this->logisticaId = $registrarUsuario->handle($logisticaCommand);
        
        // Técnico ensamblador
        $ensambladorCommand = new RegistrarUsuarioCommand(
            'Ensamblador', 'Test', '2222222222', 'ensamblador@test.com', 'password123', 'Tecnico', 'Ensamblador'
        );
        $this->ensambladorId = $registrarUsuario->handle($ensambladorCommand);
        
        // Técnico comprobador
        $comprobadorCommand = new RegistrarUsuarioCommand(
            'Comprobador', 'Test', '3333333333', 'comprobador@test.com', 'password123', 'Tecnico', 'Comprobador'
        );
        $this->comprobadorId = $registrarUsuario->handle($comprobadorCommand);
        
        // Registrar comercio
        $registrarComercio = new RegistrarComercioHandler($this->comercioRepository, 
            \maquinas_recreativas\Infrastructure\Security\HistorialHelper::getInstance());
        $comercioCommand = new RegistrarComercioCommand(
            'Comercio Test', 'Minorista', 'Dirección Test', '0999999999', $this->logisticaId->value()
        );
        $comercio = $registrarComercio->handle($comercioCommand);
        $this->comercioId = $comercio->getId();
    }
    
    /**
     * @test
     * CP-061 - Registrar máquina válida
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
     * CP-062 - Generar placa
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
     * CP-063 - Registrar montaje
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