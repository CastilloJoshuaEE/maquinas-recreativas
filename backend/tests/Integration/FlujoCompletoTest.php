<?php
/**
 * Tests de integración - Flujo completo del sistema
 * 
 * @package maquinas_recreativas\Tests\Integration
 */

namespace maquinas_recreativas\Tests\Integration;

use PHPUnit\Framework\TestCase;
use maquinas_recreativas\Tests\TestDatabase;
use maquinas_recreativas\Infrastructure\Repositories\MySQLUsuarioRepository;
use maquinas_recreativas\Infrastructure\Repositories\MySQLComercioRepository;
use maquinas_recreativas\Infrastructure\Repositories\MySQLMaquinaRepository;
use maquinas_recreativas\Infrastructure\Repositories\MySQLComponenteRepository;
use maquinas_recreativas\Infrastructure\Repositories\MySQLNotificacionRepository;
use maquinas_recreativas\Infrastructure\Repositories\MySQLReporteRepository;
use maquinas_recreativas\Infrastructure\Repositories\MySQLComentarioRepository;
use maquinas_recreativas\Infrastructure\Security\BcryptPasswordHasher;
use maquinas_recreativas\Application\Commands\Usuario\RegistrarUsuarioCommand;
use maquinas_recreativas\Application\Commands\Usuario\RegistrarUsuarioHandler;
use maquinas_recreativas\Application\Commands\Comercio\RegistrarComercioCommand;
use maquinas_recreativas\Application\Commands\Comercio\RegistrarComercioHandler;
use maquinas_recreativas\Application\Commands\Maquina\RegistrarMaquinaCommand;
use maquinas_recreativas\Application\Commands\Maquina\RegistrarMaquinaHandler;
use maquinas_recreativas\Application\Commands\Maquina\RegistrarMontajeCommand;
use maquinas_recreativas\Application\Commands\Maquina\RegistrarMontajeHandler;
use maquinas_recreativas\Application\Commands\Maquina\MandarAComprobacionCommand;
use maquinas_recreativas\Application\Commands\Maquina\MandarAComprobacionHandler;
use maquinas_recreativas\Application\Commands\Maquina\MandarADistribucionCommand;
use maquinas_recreativas\Application\Commands\Maquina\MandarADistribucionHandler;
use maquinas_recreativas\Application\Commands\Maquina\PonerOperativaCommand;
use maquinas_recreativas\Application\Commands\Maquina\PonerOperativaHandler;
use maquinas_recreativas\Application\Commands\Recaudacion\RegistrarRecaudacionCommand;
use maquinas_recreativas\Application\Commands\Recaudacion\RegistrarRecaudacionHandler;
use maquinas_recreativas\Application\Commands\Reporte\CrearReporteCommand;
use maquinas_recreativas\Application\Commands\Reporte\CrearReporteHandler;
use maquinas_recreativas\Application\Commands\Comentario\CrearComentarioCommand;
use maquinas_recreativas\Application\Commands\Comentario\CrearComentarioHandler;

class FlujoCompletoTest extends TestCase
{
    private TestDatabase $testDb;
    private MySQLUsuarioRepository $usuarioRepository;
    private MySQLComercioRepository $comercioRepository;
    private MySQLMaquinaRepository $maquinaRepository;
    private MySQLComponenteRepository $componenteRepository;
    private MySQLNotificacionRepository $notificacionRepository;
    private MySQLReporteRepository $reporteRepository;
    private MySQLComentarioRepository $comentarioRepository;
    private BcryptPasswordHasher $passwordHasher;
    
    protected function setUp(): void
    {
        $this->testDb = TestDatabase::getInstance();
        $this->testDb->cleanDatabase();
        
        $this->usuarioRepository = new MySQLUsuarioRepository($this->testDb);
        $this->comercioRepository = new MySQLComercioRepository($this->testDb);
        $this->maquinaRepository = new MySQLMaquinaRepository($this->testDb);
        $this->componenteRepository = new MySQLComponenteRepository($this->testDb);
        $this->notificacionRepository = new MySQLNotificacionRepository($this->testDb);
        $this->reporteRepository = new MySQLReporteRepository($this->testDb);
        $this->comentarioRepository = new MySQLComentarioRepository($this->testDb);
        $this->passwordHasher = new BcryptPasswordHasher();
    }
    
    /**
     * @test
     * CP-047 - Flujo completo: Registro de usuarios -> Comercio -> Máquina -> Montaje -> Comprobación -> Distribución -> Operativa -> Recaudación
     */
    public function testFlujoCompletoMaquina(): void
    {
        // 1. Registrar usuarios
        $registrarUsuario = new RegistrarUsuarioHandler($this->usuarioRepository, $this->passwordHasher);
        
        // Usuario logística
        $logisticaCommand = new RegistrarUsuarioCommand(
            'Logistica', 'Test', '1111111111', 'logistica@test.com', 'password123', 'Logistica'
        );
        $logisticaId = $registrarUsuario->handle($logisticaCommand);
        
        // Técnico ensamblador
        $ensambladorCommand = new RegistrarUsuarioCommand(
            'Ensamblador', 'Test', '2222222222', 'ensamblador@test.com', 'password123', 'Tecnico', 'Ensamblador'
        );
        $ensambladorId = $registrarUsuario->handle($ensambladorCommand);
        
        // Técnico comprobador
        $comprobadorCommand = new RegistrarUsuarioCommand(
            'Comprobador', 'Test', '3333333333', 'comprobador@test.com', 'password123', 'Tecnico', 'Comprobador'
        );
        $comprobadorId = $registrarUsuario->handle($comprobadorCommand);
        
        // Técnico mantenimiento
        $mantenimientoCommand = new RegistrarUsuarioCommand(
            'Mantenimiento', 'Test', '4444444444', 'mantenimiento@test.com', 'password123', 'Tecnico', 'Mantenimiento'
        );
        $mantenimientoId = $registrarUsuario->handle($mantenimientoCommand);
        
        // 2. Registrar comercio
        $registrarComercio = new RegistrarComercioHandler($this->comercioRepository, \maquinas_recreativas\Infrastructure\Security\HistorialHelper::getInstance());
        $comercioCommand = new RegistrarComercioCommand(
            'Comercio Test', 'Minorista', 'Dirección Test', '0999999999', $logisticaId->value()
        );
        $comercio = $registrarComercio->handle($comercioCommand);
        
        // 3. Registrar máquina
        $registrarMaquina = new RegistrarMaquinaHandler(
            $this->maquinaRepository, $this->usuarioRepository, $this->comercioRepository, $this->componenteRepository
        );
        
        $placaId = \maquinas_recreativas\Domain\Shared\ValueObjects\Uuid::v4();
        $carcasaId = \maquinas_recreativas\Domain\Shared\ValueObjects\Uuid::v4();
        
        $maquinaCommand = new RegistrarMaquinaCommand(
            'Máquina Test', 'Tipo A', $comercio->getId(), $logisticaId->value(), $placaId->value(), $carcasaId->value()
        );
        $maquinaId = new \maquinas_recreativas\Domain\Shared\ValueObjects\Uuid($registrarMaquina->handle($maquinaCommand));
        
        $this->assertNotNull($maquinaId);
        
        // 4. Registrar montaje
        $registrarMontaje = new RegistrarMontajeHandler(
            $this->maquinaRepository, $this->componenteRepository, 
            new \maquinas_recreativas\Infrastructure\Repositories\MySQLMontajeRepository($this->testDb),
            new \maquinas_recreativas\Infrastructure\Repositories\MySQLHistorialRepository($this->testDb),
            $this->usuarioRepository
        );
        
        $componenteId = \maquinas_recreativas\Domain\Shared\ValueObjects\Uuid::v4();
        $componente = \maquinas_recreativas\Domain\Componente\Componente::crear(
            \maquinas_recreativas\Domain\Componente\TipoComponente::ELECTRONICO(),
            'Componente Test',
            100.00
        );
        $this->componenteRepository->save($componente);
        
        $montajeCommand = new \maquinas_recreativas\Application\Commands\Maquina\RegistrarMontajeCommand(
            $maquinaId->value(), $componente->id()->value(), $ensambladorId->value(), 'Montaje de prueba'
        );
        $registrarMontaje->handle($montajeCommand);
        
        // 5. Enviar a comprobación
        $mandarAComprobacion = new MandarAComprobacionHandler(
            $this->maquinaRepository, $this->usuarioRepository, $this->notificacionRepository,
            new \maquinas_recreativas\Infrastructure\Repositories\MySQLHistorialRepository($this->testDb)
        );
        
        $comprobacionCommand = new MandarAComprobacionCommand(
            $maquinaId->value(), $ensambladorId->value(), 'Máquina lista para comprobación'
        );
        $mandarAComprobacion->handle($comprobacionCommand);
        
        // Verificar estado
        $maquina = $this->maquinaRepository->findById($maquinaId);
        $this->assertTrue($maquina->estado()->equals(\maquinas_recreativas\Domain\Maquina\EstadoMaquina::COMPROBANDOSE()));
        
        // 6. Enviar a distribución
        $mandarADistribucion = new MandarADistribucionHandler(
            $this->maquinaRepository, $this->usuarioRepository, $this->comercioRepository,
            new \maquinas_recreativas\Infrastructure\Repositories\MySQLDistribucionRepository($this->testDb),
            $this->notificacionRepository,
            new \maquinas_recreativas\Infrastructure\Repositories\MySQLHistorialRepository($this->testDb)
        );
        
        $distribucionCommand = new MandarADistribucionCommand(
            $maquinaId->value(), $comprobadorId->value(), 'Máquina aprobada para distribución'
        );
        $mandarADistribucion->handle($distribucionCommand);
        
        // Verificar estado
        $maquina = $this->maquinaRepository->findById($maquinaId);
        $this->assertTrue($maquina->estado()->equals(\maquinas_recreativas\Domain\Maquina\EstadoMaquina::DISTRIBUYENDOSE()));
        
        // 7. Poner operativa
        $ponerOperativa = new PonerOperativaHandler(
            $this->maquinaRepository,
            new \maquinas_recreativas\Infrastructure\Repositories\MySQLDistribucionRepository($this->testDb),
            new \maquinas_recreativas\Infrastructure\Repositories\MySQLHistorialRepository($this->testDb)
        );
        
        $operativaCommand = new PonerOperativaCommand($maquinaId->value());
        $ponerOperativa->handle($operativaCommand);
        
        // Verificar estado
        $maquina = $this->maquinaRepository->findById($maquinaId);
        $this->assertTrue($maquina->estado()->equals(\maquinas_recreativas\Domain\Maquina\EstadoMaquina::OPERATIVA()));
        $this->assertTrue($maquina->etapa()->equals(\maquinas_recreativas\Domain\Maquina\EtapaMaquina::RECAUDACION()));
        
        // 8. Registrar recaudación
        $registrarRecaudacion = new RegistrarRecaudacionHandler(
            new \maquinas_recreativas\Infrastructure\Repositories\MySQLRecaudacionRepository($this->testDb),
            $this->maquinaRepository,
            $this->usuarioRepository
        );
        
        $recaudacionCommand = new RegistrarRecaudacionCommand(
            $maquinaId->value(), $logisticaId->value(), 'Minorista', 1000.00, 30, 'Recaudación de prueba'
        );
        $recaudacionId = $registrarRecaudacion->handle($recaudacionCommand);
        
        $this->assertNotNull($recaudacionId);
        
        // Verificar recaudación guardada
        $recaudacionRepository = new \maquinas_recreativas\Infrastructure\Repositories\MySQLRecaudacionRepository($this->testDb);
        $recaudacion = $recaudacionRepository->findById(new \maquinas_recreativas\Domain\Shared\ValueObjects\Uuid($recaudacionId));
        $this->assertNotNull($recaudacion);
        $this->assertEquals(1000.00, $recaudacion->montoTotal());
        $this->assertEquals(300.00, $recaudacion->montoComercio()); // 30% de 1000
        $this->assertEquals(700.00, $recaudacion->montoEmpresa());
    }
    
    /**
     * @test
     * CP-048 - Flujo completo: Reportes y comentarios
     */
    public function testFlujoCompletoReportes(): void
    {
        // 1. Registrar usuarios
        $registrarUsuario = new RegistrarUsuarioHandler($this->usuarioRepository, $this->passwordHasher);
        
        $usuario1Command = new RegistrarUsuarioCommand(
            'Usuario', 'Uno', '1111111111', 'usuario1@test.com', 'password123', 'Usuario'
        );
        $usuario1Id = $registrarUsuario->handle($usuario1Command);
        
        $usuario2Command = new RegistrarUsuarioCommand(
            'Usuario', 'Dos', '2222222222', 'usuario2@test.com', 'password123', 'Usuario'
        );
        $usuario2Id = $registrarUsuario->handle($usuario2Command);
        
        // 2. Crear reporte
        $crearReporte = new CrearReporteHandler($this->reporteRepository, $this->notificacionRepository, $this->usuarioRepository);
        
        $reporteCommand = new CrearReporteCommand($usuario1Id->value(), $usuario2Id->value(), 'Problema con la máquina #123');
        $reporteId = $crearReporte->handle($reporteCommand);
        
        $this->assertNotNull($reporteId);
        
        // 3. Crear comentario
        $crearComentario = new CrearComentarioHandler(
            $this->comentarioRepository, $this->reporteRepository, $this->notificacionRepository, $this->usuarioRepository
        );
        
        $comentarioCommand = new CrearComentarioCommand($reporteId, $usuario2Id->value(), 'Gracias por reportar, revisaremos el problema');
        $comentarioId = $crearComentario->handle($comentarioCommand);
        
        $this->assertNotNull($comentarioId);
        
        // 4. Verificar comentario
        $comentarios = $this->comentarioRepository->findByReporte(
            new \maquinas_recreativas\Domain\Shared\ValueObjects\Uuid($reporteId),
            new \maquinas_recreativas\Domain\Shared\ValueObjects\Uuid($usuario2Id->value())
        );
        
        $this->assertIsArray($comentarios);
        $this->assertCount(1, $comentarios);
    }
    
    /**
     * @test
     * CP-049 - Transacción con rollback
     */
    public function testTransaccionConRollback(): void
    {
        $this->testDb->beginTransaction();
        
        try {
            $registrarUsuario = new RegistrarUsuarioHandler($this->usuarioRepository, $this->passwordHasher);
            
            $usuarioCommand = new RegistrarUsuarioCommand(
                'Usuario', 'Test', '9999999999', 'usuario@test.com', 'password123', 'Usuario'
            );
            $usuarioId = $registrarUsuario->handle($usuarioCommand);
            
            // Forzar error para rollback
            throw new \Exception('Error simulado');
            
            $this->testDb->commit();
        } catch (\Exception $e) {
            $this->testDb->rollback();
        }
        
        // Verificar que el usuario no se guardó
        $usuario = $this->usuarioRepository->findById($usuarioId ?? \maquinas_recreativas\Domain\Shared\ValueObjects\Uuid::v4());
        $this->assertNull($usuario);
    }
}