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
use maquinas_recreativas\Application\Commands\Usuario\CambiarEstadoUsuarioCommand;
use maquinas_recreativas\Application\Commands\Usuario\CambiarEstadoUsuarioHandler;
use maquinas_recreativas\Application\Commands\Comercio\RegistrarComercioCommand;
use maquinas_recreativas\Application\Commands\Comercio\RegistrarComercioHandler;
use maquinas_recreativas\Application\Commands\Maquina\RegistrarMaquinaCommand;
use maquinas_recreativas\Application\Commands\Maquina\RegistrarMaquinaHandler;
use maquinas_recreativas\Application\Commands\Maquina\RegistrarMontajeCommand;
use maquinas_recreativas\Application\Commands\Maquina\RegistrarMontajeHandler;
use maquinas_recreativas\Application\Commands\Maquina\GenerarPlacaCommand;
use maquinas_recreativas\Application\Commands\Maquina\GenerarPlacaHandler;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Domain\Componente\Componente;
use maquinas_recreativas\Domain\Componente\TipoComponente;

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
    private string $placaId;
    private string $carcasaId;
    
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
        $this->crearComponentesBasicos();
        $this->activarTecnicos(); // ACTIVAR TÉCNICOS ANTES DE USARLOS
    }
    
    private function activarTecnicos(): void
    {
        $cambiarEstadoHandler = new CambiarEstadoUsuarioHandler($this->usuarioRepository);
        
        // Activar técnico ensamblador
        if ($this->ensambladorId) {
            $cambiarEstadoHandler->handle(new CambiarEstadoUsuarioCommand($this->ensambladorId, 'Activo'));
            error_log("Técnico ensamblador activado: " . $this->ensambladorId->value());
        }
        
        // Activar técnico comprobador
        if ($this->comprobadorId) {
            $cambiarEstadoHandler->handle(new CambiarEstadoUsuarioCommand($this->comprobadorId, 'Activo'));
            error_log("Técnico comprobador activado: " . $this->comprobadorId->value());
        }
        
        // Activar logística
        if ($this->logisticaId) {
            $cambiarEstadoHandler->handle(new CambiarEstadoUsuarioCommand($this->logisticaId, 'Activo'));
            error_log("Usuario logística activado: " . $this->logisticaId->value());
        }
    }
    
    private function crearComponentesBasicos(): void
    {
        // Crear componente PLACA
        $placa = Componente::crear(
            TipoComponente::LOGISTICO(),
            'PL' . date('y') . '001',
            120.00
        );
        $this->componenteRepository->save($placa);
        $this->placaId = $placa->id()->value();
        
        // Crear componente CARCASA
        $carcasa = Componente::crear(
            TipoComponente::ESTRUCTURAL(),
            'Carcasa Standard',
            150.00
        );
        $this->componenteRepository->save($carcasa);
        $this->carcasaId = $carcasa->id()->value();
    }
    
    private function crearDatosBase(): void
    {
        $registrarUsuario = new RegistrarUsuarioHandler($this->usuarioRepository, $this->passwordHasher);
        
        // Usuario logística
        $logisticaCommand = new RegistrarUsuarioCommand(
            'Logistica', 'Test', 
            $this->generarCiUnico(), 
            $this->generarEmailUnico('logistica'), 
            'Logistica'
        );
        $this->logisticaId = $registrarUsuario->handle($logisticaCommand);
        
        // Técnico ensamblador
        $ensambladorCommand = new RegistrarUsuarioCommand(
            'Ensamblador', 'Test', 
            $this->generarCiUnico(), 
            $this->generarEmailUnico('ensamblador'), 
            'Tecnico', 
            'Ensamblador'
        );
        $this->ensambladorId = $registrarUsuario->handle($ensambladorCommand);
        
        // Técnico comprobador
        $comprobadorCommand = new RegistrarUsuarioCommand(
            'Comprobador', 'Test', 
            $this->generarCiUnico(), 
            $this->generarEmailUnico('comprobador'), 
            'Tecnico', 
            'Comprobador'
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
            $this->maquinaRepository, 
            $this->usuarioRepository, 
            $this->comercioRepository, 
            $this->componenteRepository,
            null  // <-- AÑADIR EL 5to PARÁMETRO (notificacionHandler)
        );
        
        $maquinaCommand = new RegistrarMaquinaCommand(
            'Máquina Test', 'Tipo A', $this->comercioId, $this->logisticaId->value(),
            $this->placaId, $this->carcasaId
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
        // Primero crear componente electrónico
        $componente = Componente::crear(
            TipoComponente::ELECTRONICO(),
            'Componente Test',
            100.00
        );
        $this->componenteRepository->save($componente);
        
        // Registrar máquina
        $registrarMaquina = new RegistrarMaquinaHandler(
            $this->maquinaRepository, 
            $this->usuarioRepository, 
            $this->comercioRepository, 
            $this->componenteRepository,
            null  // <-- AÑADIR EL 5to PARÁMETRO
        );
        
        $maquinaCommand = new RegistrarMaquinaCommand(
            'Máquina Montaje', 'Tipo A', $this->comercioId, $this->logisticaId->value(),
            $this->placaId, $this->carcasaId
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