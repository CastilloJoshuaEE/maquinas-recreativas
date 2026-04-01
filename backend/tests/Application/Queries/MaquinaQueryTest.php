<?php
/**
 * Tests de queries de máquina
 * 
 * @package maquinas_recreativas\Tests\Application\Queries
 */

namespace maquinas_recreativas\Tests\Application\Queries;

use PHPUnit\Framework\TestCase;
use maquinas_recreativas\Tests\TestDatabase;
use maquinas_recreativas\Infrastructure\Repositories\MySQLUsuarioRepository;
use maquinas_recreativas\Infrastructure\Repositories\MySQLComercioRepository;
use maquinas_recreativas\Infrastructure\Repositories\MySQLMaquinaRepository;
use maquinas_recreativas\Infrastructure\Repositories\MySQLComponenteRepository;
use maquinas_recreativas\Infrastructure\Security\BcryptPasswordHasher;
use maquinas_recreativas\Application\Commands\Usuario\RegistrarUsuarioCommand;
use maquinas_recreativas\Application\Commands\Usuario\RegistrarUsuarioHandler;
use maquinas_recreativas\Application\Commands\Comercio\RegistrarComercioCommand;
use maquinas_recreativas\Application\Commands\Comercio\RegistrarComercioHandler;
use maquinas_recreativas\Application\Commands\Maquina\RegistrarMaquinaCommand;
use maquinas_recreativas\Application\Commands\Maquina\RegistrarMaquinaHandler;
use maquinas_recreativas\Application\Queries\Maquina\ObtenerMaquinasPorEstadoQuery;
use maquinas_recreativas\Application\Queries\Maquina\ObtenerMaquinasPorEstadoHandler;
use maquinas_recreativas\Application\Queries\Maquina\ObtenerMaquinasPorEtapaQuery;
use maquinas_recreativas\Application\Queries\Maquina\ObtenerMaquinasPorEtapaHandler;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;

class MaquinaQueryTest extends TestCase
{
    private TestDatabase $testDb;
    private MySQLUsuarioRepository $usuarioRepository;
    private MySQLComercioRepository $comercioRepository;
    private MySQLMaquinaRepository $maquinaRepository;
    private MySQLComponenteRepository $componenteRepository;
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
        $this->passwordHasher = new BcryptPasswordHasher();
        
        $this->crearDatosBase();
        $this->crearMaquinasPrueba();
    }
    
    private function crearDatosBase(): void
    {
        $registrarUsuario = new RegistrarUsuarioHandler($this->usuarioRepository, $this->passwordHasher);
        
        $logisticaCommand = new RegistrarUsuarioCommand(
            'Logistica', 'Test', '1111111111', 'logistica@test.com', 'password123', 'Logistica'
        );
        $this->logisticaId = $registrarUsuario->handle($logisticaCommand);
        
        $ensambladorCommand = new RegistrarUsuarioCommand(
            'Ensamblador', 'Test', '2222222222', 'ensamblador@test.com', 'password123', 'Tecnico', 'Ensamblador'
        );
        $this->ensambladorId = $registrarUsuario->handle($ensambladorCommand);
        
        $comprobadorCommand = new RegistrarUsuarioCommand(
            'Comprobador', 'Test', '3333333333', 'comprobador@test.com', 'password123', 'Tecnico', 'Comprobador'
        );
        $this->comprobadorId = $registrarUsuario->handle($comprobadorCommand);
        
        $registrarComercio = new RegistrarComercioHandler($this->comercioRepository, 
            \maquinas_recreativas\Infrastructure\Security\HistorialHelper::getInstance());
        $comercioCommand = new RegistrarComercioCommand(
            'Comercio Test', 'Minorista', 'Dirección Test', '0999999999', $this->logisticaId->value()
        );
        $comercio = $registrarComercio->handle($comercioCommand);
        $this->comercioId = $comercio->getId();
    }
    
    private function crearMaquinasPrueba(): void
    {
        $registrarMaquina = new RegistrarMaquinaHandler(
            $this->maquinaRepository, $this->usuarioRepository, $this->comercioRepository, $this->componenteRepository
        );
        
        $placaId = Uuid::v4();
        $carcasaId = Uuid::v4();
        
        // Máquina en montaje
        $maquinaCommand1 = new RegistrarMaquinaCommand(
            'Máquina Montaje', 'Tipo A', $this->comercioId, $this->logisticaId->value(),
            $placaId->value(), $carcasaId->value()
        );
        $maquinaId1 = $registrarMaquina->handle($maquinaCommand1);
        
        // Máquina en comprobación
        $maquinaCommand2 = new RegistrarMaquinaCommand(
            'Máquina Comprobacion', 'Tipo B', $this->comercioId, $this->logisticaId->value(),
            $placaId->value(), $carcasaId->value()
        );
        $maquinaId2 = $registrarMaquina->handle($maquinaCommand2);
        
        $maquina2 = $this->maquinaRepository->findById(new Uuid($maquinaId2));
        $maquina2->enviarAComprobacion();
        $this->maquinaRepository->save($maquina2);
    }
    
    /**
     * @test
     * CP-067 - Obtener máquinas por estado
     */
    public function testObtenerMaquinasPorEstado(): void
    {
        $handler = new ObtenerMaquinasPorEstadoHandler($this->maquinaRepository);
        
        $query = new ObtenerMaquinasPorEstadoQuery('Ensamblandose');
        $maquinas = $handler->handle($query);
        
        $this->assertIsArray($maquinas);
        $this->assertCount(1, $maquinas);
        $this->assertEquals('Máquina Montaje', $maquinas[0]['nombre']);
        
        $query2 = new ObtenerMaquinasPorEstadoQuery('Comprobandose');
        $maquinas2 = $handler->handle($query2);
        
        $this->assertCount(1, $maquinas2);
        $this->assertEquals('Máquina Comprobacion', $maquinas2[0]['nombre']);
    }
    
    /**
     * @test
     * CP-068 - Obtener máquinas por etapa
     */
    public function testObtenerMaquinasPorEtapa(): void
    {
        $handler = new ObtenerMaquinasPorEtapaHandler($this->maquinaRepository);
        
        $query = new ObtenerMaquinasPorEtapaQuery('Montaje');
        $maquinas = $handler->handle($query);
        
        $this->assertIsArray($maquinas);
        $this->assertCount(1, $maquinas);
        $this->assertEquals('Máquina Montaje', $maquinas[0]['nombre']);
    }
    
    /**
     * @test
     * CP-069 - Estado inválido lanza excepción
     */
    public function testEstadoInvalidoLanzaExcepcion(): void
    {
        $handler = new ObtenerMaquinasPorEstadoHandler($this->maquinaRepository);
        
        $query = new ObtenerMaquinasPorEstadoQuery('EstadoInvalido');
        
        $this->expectException(\maquinas_recreativas\Domain\Shared\Exceptions\DomainException::class);
        
        $handler->handle($query);
    }
    
    /**
     * @test
     * CP-070 - Etapa inválida lanza excepción
     */
    public function testEtapaInvalidaLanzaExcepcion(): void
    {
        $handler = new ObtenerMaquinasPorEtapaHandler($this->maquinaRepository);
        
        $query = new ObtenerMaquinasPorEtapaQuery('EtapaInvalida');
        
        $this->expectException(\maquinas_recreativas\Domain\Shared\Exceptions\DomainException::class);
        
        $handler->handle($query);
    }
}