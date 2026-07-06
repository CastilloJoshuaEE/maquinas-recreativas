<?php
/**
 * Tests de queries de máquina
 */

namespace maquinas_recreativas\Tests\Application\Queries;

use PHPUnit\Framework\TestCase;
use maquinas_recreativas\Tests\TestDatabase;
use maquinas_recreativas\Infrastructure\Repositories\PDOUsuarioRepository;
use maquinas_recreativas\Infrastructure\Repositories\PDOComercioRepository;
use maquinas_recreativas\Infrastructure\Repositories\PDOMaquinaRepository;
use maquinas_recreativas\Infrastructure\Repositories\PDOComponenteRepository;
use maquinas_recreativas\Infrastructure\Security\BcryptPasswordHasher;
use maquinas_recreativas\Application\Commands\Usuario\RegistrarUsuarioCommand;
use maquinas_recreativas\Application\Commands\Usuario\RegistrarUsuarioHandler;
use maquinas_recreativas\Application\Commands\Usuario\CambiarEstadoUsuarioCommand;
use maquinas_recreativas\Application\Commands\Usuario\CambiarEstadoUsuarioHandler;
use maquinas_recreativas\Application\Commands\Comercio\RegistrarComercioCommand;
use maquinas_recreativas\Application\Commands\Comercio\RegistrarComercioHandler;
use maquinas_recreativas\Application\Commands\Maquina\RegistrarMaquinaCommand;
use maquinas_recreativas\Application\Commands\Maquina\RegistrarMaquinaHandler;
use maquinas_recreativas\Application\Queries\Maquina\ObtenerMaquinasPorEstadoQuery;
use maquinas_recreativas\Application\Queries\Maquina\ObtenerMaquinasPorEstadoHandler;
use maquinas_recreativas\Application\Queries\Maquina\ObtenerMaquinasPorEtapaQuery;
use maquinas_recreativas\Application\Queries\Maquina\ObtenerMaquinasPorEtapaHandler;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;
use maquinas_recreativas\Domain\Componente\Componente;
use maquinas_recreativas\Domain\Componente\TipoComponente;

class MaquinaQueryTest extends TestCase
{
    private TestDatabase $testDb;
    private PDOUsuarioRepository $usuarioRepository;
    private PDOComercioRepository $comercioRepository;
    private PDOMaquinaRepository $maquinaRepository;
    private PDOComponenteRepository $componenteRepository;
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
        
        $this->usuarioRepository = new PDOUsuarioRepository($this->testDb);
        $this->comercioRepository = new PDOComercioRepository($this->testDb);
        $this->maquinaRepository = new PDOMaquinaRepository($this->testDb);
        $this->componenteRepository = new PDOComponenteRepository($this->testDb);
        $this->passwordHasher = new BcryptPasswordHasher();
        
        $this->crearDatosBase();
        $this->crearComponentesBasicos();
        $this->activarTecnicos(); // ACTIVAR TÉCNICOS ANTES DE CREAR MÁQUINAS
        $this->crearMaquinasPrueba();
    }
    
    private function activarTecnicos(): void
    {
        $cambiarEstadoHandler = new CambiarEstadoUsuarioHandler($this->usuarioRepository);
        
        if ($this->ensambladorId) {
            $cambiarEstadoHandler->handle(new CambiarEstadoUsuarioCommand($this->ensambladorId, 'Activo'));
            error_log("QueryTest - Técnico ensamblador activado: " . $this->ensambladorId->value());
        }
        
        if ($this->comprobadorId) {
            $cambiarEstadoHandler->handle(new CambiarEstadoUsuarioCommand($this->comprobadorId, 'Activo'));
            error_log("QueryTest - Técnico comprobador activado: " . $this->comprobadorId->value());
        }
        
        if ($this->logisticaId) {
            $cambiarEstadoHandler->handle(new CambiarEstadoUsuarioCommand($this->logisticaId, 'Activo'));
            error_log("QueryTest - Logística activado: " . $this->logisticaId->value());
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
    
private function crearMaquinasPrueba(): void
{
    $registrarMaquina = new RegistrarMaquinaHandler(
        $this->maquinaRepository, 
        $this->usuarioRepository, 
        $this->comercioRepository, 
        $this->componenteRepository,
        null
    );
    
    // Máquina en montaje - INCLUYENDO TÉCNICOS
    $maquinaCommand1 = new RegistrarMaquinaCommand(
        'Máquina Montaje', 
        'Tipo A', 
        $this->comercioId, 
        $this->logisticaId->value(),
        $this->placaId, 
        $this->carcasaId,
        $this->ensambladorId->value(),   
        $this->comprobadorId->value()    
    );
    $maquinaId1 = $registrarMaquina->handle($maquinaCommand1);
    
    // Máquina en comprobación - INCLUYENDO TÉCNICOS
    $maquinaCommand2 = new RegistrarMaquinaCommand(
        'Máquina Comprobacion', 
        'Tipo B', 
        $this->comercioId, 
        $this->logisticaId->value(),
        $this->placaId, 
        $this->carcasaId,
        $this->ensambladorId->value(),   
        $this->comprobadorId->value()    
    );
    $maquinaId2 = $registrarMaquina->handle($maquinaCommand2);
        $maquina2 = $this->maquinaRepository->findById(new Uuid($maquinaId2));
        if ($maquina2) {
            $maquina2->enviarAComprobacion();
            $this->maquinaRepository->save($maquina2);
        }
    }
    
    /**
     * @test
     */
    public function testObtenerMaquinasPorEstado(): void
    {
        $handler = new ObtenerMaquinasPorEstadoHandler($this->maquinaRepository);
        
        $query = new ObtenerMaquinasPorEstadoQuery('Ensamblandose');
        $resultado = $handler->handle($query);
        
        $this->assertIsArray($resultado);
        $this->assertTrue($resultado['success']);
        $this->assertGreaterThanOrEqual(1, count($resultado['maquinas']));
    }
    
    /**
     * @test
     */
    public function testObtenerMaquinasPorEtapa(): void
    {
        $handler = new ObtenerMaquinasPorEtapaHandler($this->maquinaRepository);
        
        $query = new ObtenerMaquinasPorEtapaQuery('Montaje');
        $resultado = $handler->handle($query);
        
        $this->assertIsArray($resultado);
        $this->assertTrue($resultado['success']);
        $this->assertGreaterThanOrEqual(1, count($resultado['maquinas']));
    }
    
    /**
     * @test
     */
    public function testEstadoInvalidoLanzaExcepcion(): void
    {
        $handler = new ObtenerMaquinasPorEstadoHandler($this->maquinaRepository);
        $query = new ObtenerMaquinasPorEstadoQuery('EstadoInvalido');
        
        $resultado = $handler->handle($query);
        
        $this->assertFalse($resultado['success']);
        $this->assertArrayHasKey('error', $resultado);
    }
    
    /**
     * @test
     */
    public function testEtapaInvalidaLanzaExcepcion(): void
    {
        $handler = new ObtenerMaquinasPorEtapaHandler($this->maquinaRepository);
        $query = new ObtenerMaquinasPorEtapaQuery('EtapaInvalida');
        
        $resultado = $handler->handle($query);
        
        $this->assertFalse($resultado['success']);
        $this->assertArrayHasKey('error', $resultado);
    }
}