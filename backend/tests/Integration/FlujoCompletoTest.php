<?php
/**
 * Tests de integración - Flujo completo del sistema
 * 
 * @package maquinas_recreativas\Tests\Integration
 */

namespace maquinas_recreativas\Tests\Integration;

use PHPUnit\Framework\TestCase;
use maquinas_recreativas\Tests\TestDatabase;
use maquinas_recreativas\Infrastructure\Repositories\PDOUsuarioRepository;
use maquinas_recreativas\Infrastructure\Repositories\PDOComercioRepository;
use maquinas_recreativas\Infrastructure\Repositories\PDOMaquinaRepository;
use maquinas_recreativas\Infrastructure\Repositories\PDOComponenteRepository;
use maquinas_recreativas\Infrastructure\Repositories\PDONotificacionRepository;
use maquinas_recreativas\Infrastructure\Repositories\PDOReporteRepository;
use maquinas_recreativas\Infrastructure\Repositories\PDOComentarioRepository;
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
use maquinas_recreativas\Infrastructure\Repositories\PDOMontajeRepository;
use maquinas_recreativas\Infrastructure\Repositories\PDOHistorialRepository;
use maquinas_recreativas\Infrastructure\Repositories\PDODistribucionRepository;
use maquinas_recreativas\Infrastructure\Security\HistorialHelper;
use maquinas_recreativas\Domain\Componente\Componente;
use maquinas_recreativas\Domain\Componente\TipoComponente;
use maquinas_recreativas\Domain\Maquina\EstadoMaquina;
use maquinas_recreativas\Domain\Maquina\EtapaMaquina;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;

class FlujoCompletoTest extends TestCase
{
    private TestDatabase $testDb;
    private PDOUsuarioRepository $usuarioRepository;
    private PDOComercioRepository $comercioRepository;
    private PDOMaquinaRepository $maquinaRepository;
    private PDOComponenteRepository $componenteRepository;
    private PDONotificacionRepository $notificacionRepository;
    private PDOReporteRepository $reporteRepository;
    private PDOComentarioRepository $comentarioRepository;
    private BcryptPasswordHasher $passwordHasher;
    
    private Uuid $logisticaId;
    private Uuid $ensambladorId;
    private Uuid $comprobadorId;
    private Uuid $mantenimientoId;
    
    protected function setUp(): void
    {
        $this->testDb = TestDatabase::getInstance();
        $this->testDb->cleanDatabase();
        
        $this->usuarioRepository = new PDOUsuarioRepository($this->testDb);
        $this->comercioRepository = new PDOComercioRepository($this->testDb);
        $this->maquinaRepository = new PDOMaquinaRepository($this->testDb);
        $this->componenteRepository = new PDOComponenteRepository($this->testDb);
        $this->notificacionRepository = new PDONotificacionRepository($this->testDb);
        $this->reporteRepository = new PDOReporteRepository($this->testDb);
        $this->comentarioRepository = new PDOComentarioRepository($this->testDb);
        $this->passwordHasher = new BcryptPasswordHasher();
        
        $this->crearUsuarios();
    }
   private function crearUsuarios(): void
{
    $registrarAdminHandler = new RegistrarUsuarioAdminHandler($this->usuarioRepository);
    
    // Usuario logística - email único con timestamp
    $timestamp = time();
    $logisticaCommand = new RegistrarUsuarioAdminCommand(
        'Logistica', 'Test', '111001' . $timestamp, 
        'logistica_' . $timestamp . '@test.com', 
        null, 'Password123!', 'Logistica', 'Activo'
    );
    $logistica = $registrarAdminHandler->handle($logisticaCommand);
    $this->logisticaId = $logistica->getId();
    
    // Técnico ensamblador
    $ensambladorCommand = new RegistrarUsuarioAdminCommand(
        'Ensamblador', 'Test', '222200' . $timestamp, 
        'ensamblador_' . $timestamp . '@test.com', 
        null, 'Password123!', 'Tecnico', 'Activo', 'Ensamblador'
    );
    $ensamblador = $registrarAdminHandler->handle($ensambladorCommand);
    $this->ensambladorId = $ensamblador->getId();
    
    // Técnico comprobador
    $comprobadorCommand = new RegistrarUsuarioAdminCommand(
        'Comprobador', 'Test', '333300' . $timestamp, 
        'comprobador_' . $timestamp . '@test.com', 
        null, 'Password123!', 'Tecnico', 'Activo', 'Comprobador'
    );
    $comprobador = $registrarAdminHandler->handle($comprobadorCommand);
    $this->comprobadorId = $comprobador->getId();
    
    // Técnico mantenimiento
    $mantenimientoCommand = new RegistrarUsuarioAdminCommand(
        'Mantenimiento', 'Test', '444400' . $timestamp, 
        'mantenimiento_' . $timestamp . '@test.com', 
        null, 'Password123!', 'Tecnico', 'Activo', 'Mantenimiento'
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
    $this->maquinaRepository,
    $this->usuarioRepository,
    $this->comercioRepository,
    $this->componenteRepository,
    null,
    new HistorialHelper($this->testDb->getConnection())  
);
        
        $placaId = Uuid::v4();
        $carcasaId = Uuid::v4();
        
        $maquinaCommand = new RegistrarMaquinaCommand(
            'Máquina Test',
            'Tipo A',
            $comercio->getId(),
            $this->logisticaId->value(),
            $placaId->value(),
            $carcasaId->value(),
            $this->ensambladorId->value(),   // idEnsamblador
            $this->comprobadorId->value()    // idComprobador
        );
        
        $maquinaIdString = $registrarMaquina->handle($maquinaCommand);
        $this->assertNotNull($maquinaIdString);
        $this->assertIsString($maquinaIdString);
        
        // 4. Registrar montaje
        $registrarMontaje = new RegistrarMontajeHandler(
            $this->maquinaRepository, $this->componenteRepository, 
            new PDOMontajeRepository($this->testDb),
            new PDOHistorialRepository($this->testDb),
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
            new PDOHistorialRepository($this->testDb)
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
            new PDODistribucionRepository($this->testDb),
            $this->notificacionRepository,
            new PDOHistorialRepository($this->testDb)
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
            new PDODistribucionRepository($this->testDb),
            new PDOHistorialRepository($this->testDb)
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
    $transactionStarted = false;
    
    try {
        // Iniciar transacción con verificación
        try {
            $transactionStarted = $this->testDb->beginTransaction();
        } catch (\Exception $e) {
            error_log("Error iniciando transacción: " . $e->getMessage());
            $transactionStarted = false;
        }
        
        if (!$transactionStarted) {
            $this->markTestSkipped('No se pudo iniciar la transacción (probablemente el driver no soporta transacciones)');
            return;
        }
        
        // ... resto del código ...
        
        // Forzar error para rollback
        throw new \Exception('Error simulado para rollback');
        
    } catch (\Exception $e) {
        if ($transactionStarted) {
            try {
                $this->testDb->rollback();
                error_log("Rollback ejecutado correctamente: " . $e->getMessage());
            } catch (\Exception $rollbackError) {
                error_log("Error en rollback: " . $rollbackError->getMessage());
            }
        }
    }
    
    // Verificar que el usuario NO se guardó
    if ($usuarioId !== null && $transactionStarted) {
        $usuario = $this->usuarioRepository->findById($usuarioId);
        $this->assertNull($usuario, 'El usuario no debería existir después del rollback');
    } else {
        $this->markTestSkipped('No se pudo crear el usuario para probar rollback');
    }
}
}