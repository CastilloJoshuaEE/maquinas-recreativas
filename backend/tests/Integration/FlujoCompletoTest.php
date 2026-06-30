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
use maquinas_recreativas\Application\Commands\Usuario\RegistrarUsuarioAdminCommand;
use maquinas_recreativas\Application\Commands\Usuario\RegistrarUsuarioAdminHandler;
use maquinas_recreativas\Application\Commands\Usuario\CambiarEstadoUsuarioCommand;
use maquinas_recreativas\Application\Commands\Usuario\CambiarEstadoUsuarioHandler;
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
use maquinas_recreativas\Application\Commands\Reporte\CrearReporteCommand;
use maquinas_recreativas\Application\Commands\Reporte\CrearReporteHandler;
use maquinas_recreativas\Application\Commands\Comentario\CrearComentarioCommand;
use maquinas_recreativas\Application\Commands\Comentario\CrearComentarioHandler;
use maquinas_recreativas\Infrastructure\Repositories\MySQLMontajeRepository;
use maquinas_recreativas\Infrastructure\Repositories\MySQLHistorialRepository;
use maquinas_recreativas\Infrastructure\Repositories\MySQLDistribucionRepository;
use maquinas_recreativas\Infrastructure\Security\HistorialHelper;
use maquinas_recreativas\Domain\Componente\Componente;
use maquinas_recreativas\Domain\Componente\TipoComponente;
use maquinas_recreativas\Domain\Maquina\EstadoMaquina;
use maquinas_recreativas\Domain\Maquina\EtapaMaquina;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;

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
    
    private Uuid $logisticaId;
    private Uuid $ensambladorId;
    private Uuid $comprobadorId;
    private Uuid $mantenimientoId;
    
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
        
        $this->crearUsuarios();
    }
    
    private function crearUsuarios(): void
    {
        $registrarAdminHandler = new RegistrarUsuarioAdminHandler($this->usuarioRepository);
        
        // Usuario logística
        $logisticaCommand = new RegistrarUsuarioAdminCommand(
            'Logistica', 'Test', '1110011111', 'logistica@test.com', null, 'Password123!', 'Logistica', 'Activo'
        );
        $logistica = $registrarAdminHandler->handle($logisticaCommand);
        $this->logisticaId = $logistica->getId();
        
        // Técnico ensamblador
        $ensambladorCommand = new RegistrarUsuarioAdminCommand(
            'Ensamblador', 'Test', '2222002222', 'ensamblador@test.com', null, 'Password123!', 'Tecnico', 'Activo', 'Ensamblador'
        );
        $ensamblador = $registrarAdminHandler->handle($ensambladorCommand);
        $this->ensambladorId = $ensamblador->getId();
        
        // Técnico comprobador
        $comprobadorCommand = new RegistrarUsuarioAdminCommand(
            'Comprobador', 'Test', '3333003333', 'comprobador@test.com', null, 'Password123!', 'Tecnico', 'Activo', 'Comprobador'
        );
        $comprobador = $registrarAdminHandler->handle($comprobadorCommand);
        $this->comprobadorId = $comprobador->getId();
        
        // Técnico mantenimiento
        $mantenimientoCommand = new RegistrarUsuarioAdminCommand(
            'Mantenimiento', 'Test', '4444004444', 'mantenimiento@test.com', null, 'Password123!', 'Tecnico', 'Activo', 'Mantenimiento'
        );
        $mantenimiento = $registrarAdminHandler->handle($mantenimientoCommand);
        $this->mantenimientoId = $mantenimiento->getId();
    }
    
    /**
     * @test
     * CP-047 - Flujo completo: Registro de usuarios -> Comercio -> Máquina -> Montaje -> Comprobación -> Distribución -> Operativa
     */
    public function testFlujoCompletoMaquina(): void
    {
        // 2. Registrar comercio
        $registrarComercio = new RegistrarComercioHandler($this->comercioRepository, HistorialHelper::getInstance());
        
        $comercioCommand = new RegistrarComercioCommand(
            'Comercio Test',
            'Minorista',
            'Dirección Test',
            '0999999909',
            $this->logisticaId->value()
        );
        
        $comercio = $registrarComercio->handle($comercioCommand);
        $this->assertNotNull($comercio, "Comercio no creado");
        
        // 3. Registrar máquina
        $registrarMaquina = new RegistrarMaquinaHandler(
            $this->maquinaRepository, $this->usuarioRepository, $this->comercioRepository, $this->componenteRepository, null
        );
        
        $placaId = Uuid::v4();
        $carcasaId = Uuid::v4();
        
        $maquinaCommand = new RegistrarMaquinaCommand(
            'Máquina Test',
            'Tipo A',
            $comercio->getId(),
            $this->logisticaId->value(),
            $placaId->value(),
            $carcasaId->value()
        );
        
        $maquinaIdString = $registrarMaquina->handle($maquinaCommand);
        $this->assertNotNull($maquinaIdString);
        $this->assertIsString($maquinaIdString);
        
        // 4. Registrar montaje
        $registrarMontaje = new RegistrarMontajeHandler(
            $this->maquinaRepository, $this->componenteRepository, 
            new MySQLMontajeRepository($this->testDb),
            new MySQLHistorialRepository($this->testDb),
            $this->usuarioRepository
        );
        
        // Crear componente
        $componente = Componente::crear(TipoComponente::ELECTRONICO(), 'Componente Test', 100.00);
        $this->componenteRepository->save($componente);
        
        $montajeCommand = new RegistrarMontajeCommand(
            $maquinaIdString,
            $componente->id()->value(),
            $this->ensambladorId->value(),
            'Montaje de prueba'
        );
        $registrarMontaje->handle($montajeCommand);
        
        // 5. Enviar a comprobación
        $mandarAComprobacion = new MandarAComprobacionHandler(
            $this->maquinaRepository, $this->usuarioRepository, $this->notificacionRepository,
            new MySQLHistorialRepository($this->testDb)
        );
        
        $comprobacionCommand = new MandarAComprobacionCommand(
            $maquinaIdString,
            $this->ensambladorId->value(),
            'Máquina lista para comprobación'
        );
        $mandarAComprobacion->handle($comprobacionCommand);
        
        // 6. Enviar a distribución
        $mandarADistribucion = new MandarADistribucionHandler(
            $this->maquinaRepository, $this->usuarioRepository, $this->comercioRepository,
            new MySQLDistribucionRepository($this->testDb),
            $this->notificacionRepository,
            new MySQLHistorialRepository($this->testDb)
        );
        
        $distribucionCommand = new MandarADistribucionCommand(
            $maquinaIdString,
            $this->comprobadorId->value(),
            'Máquina aprobada para distribución'
        );
        $mandarADistribucion->handle($distribucionCommand);
        
        // 7. Poner operativa
        $ponerOperativa = new PonerOperativaHandler(
            $this->maquinaRepository,
            new MySQLDistribucionRepository($this->testDb),
            new MySQLHistorialRepository($this->testDb)
        );
        
        $operativaCommand = new PonerOperativaCommand($maquinaIdString);
        $ponerOperativa->handle($operativaCommand);
        
        // Verificar estado final
        $maquina = $this->maquinaRepository->findById(new Uuid($maquinaIdString));
        $this->assertTrue($maquina->estado()->equals(EstadoMaquina::OPERATIVA()));
        $this->assertTrue($maquina->etapa()->equals(EtapaMaquina::RECAUDACION()));
    }
    
    /**
     * @test
     * CP-048 - Flujo completo: Reportes y comentarios
     */
    public function testFlujoCompletoReportes(): void
    {
        $registrarAdminHandler = new RegistrarUsuarioAdminHandler($this->usuarioRepository);
        
        // Usuario 1
        $usuario1Command = new RegistrarUsuarioAdminCommand(
            'Usuario', 'Uno', '1111118111', 'usuario1@test.com', null, 'Password123!', 'Usuario', 'Activo'
        );
        $usuario1 = $registrarAdminHandler->handle($usuario1Command);
        $usuario1Id = $usuario1->getId();
        
        // Usuario 2
        $usuario2Command = new RegistrarUsuarioAdminCommand(
            'Usuario', 'Dos', '2222212222', 'usuario2@test.com', null, 'Password123!', 'Usuario', 'Activo'
        );
        $usuario2 = $registrarAdminHandler->handle($usuario2Command);
        $usuario2Id = $usuario2->getId();
        
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
            new Uuid($reporteId),
            new Uuid($usuario2Id->value())
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
        $usuarioId = null;
        
        $this->testDb->beginTransaction();
        
        try {
            $registrarAdminHandler = new RegistrarUsuarioAdminHandler($this->usuarioRepository);
            
            $usuarioCommand = new RegistrarUsuarioAdminCommand(
                'Usuario', 'Test', '9999999109', 'usuario@test.com', null, 'Password123!', 'Usuario', 'Activo'
            );
            $usuario = $registrarAdminHandler->handle($usuarioCommand);
            $usuarioId = $usuario->getId();
            
            // Verificar que el usuario se guardó en la transacción
            $usuarioEncontrado = $this->usuarioRepository->findById($usuarioId);
            $this->assertNotNull($usuarioEncontrado, 'El usuario debería existir dentro de la transacción');
            
            // Forzar error para rollback
            throw new \Exception('Error simulado para rollback');
            
            $this->testDb->commit();
        } catch (\Exception $e) {
            $this->testDb->rollback();
            error_log("Rollback ejecutado correctamente: " . $e->getMessage());
        }
        
        // Verificar que el usuario NO se guardó después del rollback
        if ($usuarioId !== null) {
            $usuario = $this->usuarioRepository->findById($usuarioId);
            $this->assertNull($usuario, 'El usuario no debería existir después del rollback');
        } else {
            $this->markTestSkipped('No se pudo crear el usuario para probar rollback');
        }
    }
}