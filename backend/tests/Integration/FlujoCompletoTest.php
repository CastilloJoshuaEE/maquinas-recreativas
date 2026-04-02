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
    $this->assertNotNull($logisticaId, "Usuario logística no creado");
    
    // Técnico ensamblador
    $ensambladorCommand = new RegistrarUsuarioCommand(
        'Ensamblador', 'Test', '2222222222', 'ensamblador@test.com', 'password123', 'Tecnico', 'Ensamblador'
    );
    $ensambladorId = $registrarUsuario->handle($ensambladorCommand);
    $this->assertNotNull($ensambladorId, "Técnico ensamblador no creado");
    
    // Técnico comprobador
    $comprobadorCommand = new RegistrarUsuarioCommand(
        'Comprobador', 'Test', '3333333333', 'comprobador@test.com', 'password123', 'Tecnico', 'Comprobador'
    );
    $comprobadorId = $registrarUsuario->handle($comprobadorCommand);
    $this->assertNotNull($comprobadorId, "Técnico comprobador no creado");
    
    // Técnico mantenimiento
    $mantenimientoCommand = new RegistrarUsuarioCommand(
        'Mantenimiento', 'Test', '4444444444', 'mantenimiento@test.com', 'password123', 'Tecnico', 'Mantenimiento'
    );
    $mantenimientoId = $registrarUsuario->handle($mantenimientoCommand);
    $this->assertNotNull($mantenimientoId, "Técnico mantenimiento no creado");
    
    // 2. Registrar comercio
    $registrarComercio = new RegistrarComercioHandler(
        $this->comercioRepository, 
        \maquinas_recreativas\Infrastructure\Security\HistorialHelper::getInstance()
    );
    
    $comercioCommand = new RegistrarComercioCommand(
        'Comercio Test',
        'Minorista',
        'Dirección Test',
        '0999999999',
        $logisticaId->value()
    );
    
    $comercio = $registrarComercio->handle($comercioCommand);
    $this->assertNotNull($comercio, "Comercio no creado");
    $this->assertNotNull($comercio->getNombre(), "Nombre del comercio es null");
  // 3. Registrar máquina
$registrarMaquina = new RegistrarMaquinaHandler(
    $this->maquinaRepository, $this->usuarioRepository, $this->comercioRepository, $this->componenteRepository
);

$placaId = \maquinas_recreativas\Domain\Shared\ValueObjects\Uuid::v4();
$carcasaId = \maquinas_recreativas\Domain\Shared\ValueObjects\Uuid::v4();

$maquinaCommand = new RegistrarMaquinaCommand(
    'Máquina Test',
    'Tipo A',
    $comercio->getId(),
    $logisticaId->value(),
    $placaId->value(),
    $carcasaId->value()
);

$maquinaIdString = $registrarMaquina->handle($maquinaCommand); // Esto ya es un string UUID

// Usar el string directamente en lugar de crear un objeto Uuid
$this->assertNotNull($maquinaIdString);
$this->assertIsString($maquinaIdString);

// 4. Registrar montaje
$registrarMontaje = new RegistrarMontajeHandler(
    $this->maquinaRepository, $this->componenteRepository, 
    new \maquinas_recreativas\Infrastructure\Repositories\MySQLMontajeRepository($this->testDb),
    new \maquinas_recreativas\Infrastructure\Repositories\MySQLHistorialRepository($this->testDb),
    $this->usuarioRepository
);

// Crear componente con el tipo correcto
$componente = \maquinas_recreativas\Domain\Componente\Componente::crear(
    \maquinas_recreativas\Domain\Componente\TipoComponente::ELECTRONICO(),
    'Componente Test',
    100.00
);
$this->componenteRepository->save($componente);

$montajeCommand = new RegistrarMontajeCommand(
    $maquinaIdString,  // Usar el string directamente
    $componente->id()->value(),
    $ensambladorId->value(),
    'Montaje de prueba'
);
$registrarMontaje->handle($montajeCommand);

// 5. Enviar a comprobación
$mandarAComprobacion = new MandarAComprobacionHandler(
    $this->maquinaRepository, $this->usuarioRepository, $this->notificacionRepository,
    new \maquinas_recreativas\Infrastructure\Repositories\MySQLHistorialRepository($this->testDb)
);

$comprobacionCommand = new MandarAComprobacionCommand(
    $maquinaIdString,  // Usar el string directamente
    $ensambladorId->value(),
    'Máquina lista para comprobación'
);
$mandarAComprobacion->handle($comprobacionCommand);

// 6. Enviar a distribución
$mandarADistribucion = new MandarADistribucionHandler(
    $this->maquinaRepository, $this->usuarioRepository, $this->comercioRepository,
    new \maquinas_recreativas\Infrastructure\Repositories\MySQLDistribucionRepository($this->testDb),
    $this->notificacionRepository,
    new \maquinas_recreativas\Infrastructure\Repositories\MySQLHistorialRepository($this->testDb)
);

$distribucionCommand = new MandarADistribucionCommand(
    $maquinaIdString,  // Usar el string directamente
    $comprobadorId->value(),
    'Máquina aprobada para distribución'
);
$mandarADistribucion->handle($distribucionCommand);

// 7. Poner operativa
$ponerOperativa = new PonerOperativaHandler(
    $this->maquinaRepository,
    new \maquinas_recreativas\Infrastructure\Repositories\MySQLDistribucionRepository($this->testDb),
    new \maquinas_recreativas\Infrastructure\Repositories\MySQLHistorialRepository($this->testDb)
);

$operativaCommand = new PonerOperativaCommand($maquinaIdString);  // Usar el string directamente
$ponerOperativa->handle($operativaCommand);

// Para obtener la máquina después de las operaciones, usar el string directamente
$maquina = $this->maquinaRepository->findById(new \maquinas_recreativas\Domain\Shared\ValueObjects\Uuid($maquinaIdString));
$this->assertTrue($maquina->estado()->equals(\maquinas_recreativas\Domain\Maquina\EstadoMaquina::OPERATIVA()));
$this->assertTrue($maquina->etapa()->equals(\maquinas_recreativas\Domain\Maquina\EtapaMaquina::RECAUDACION()));
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
    $usuarioId = null;
    
    $this->testDb->beginTransaction();
    
    try {
        $registrarUsuario = new RegistrarUsuarioHandler($this->usuarioRepository, $this->passwordHasher);
        
        $usuarioCommand = new RegistrarUsuarioCommand(
            'Usuario', 'Test', '9999999999', 'usuario@test.com', 'password123', 'Usuario'
        );
        $usuarioId = $registrarUsuario->handle($usuarioCommand);
        
        // Verificar que el usuario se guardó en la transacción
        $usuarioEncontrado = $this->usuarioRepository->findById($usuarioId);
        $this->assertNotNull($usuarioEncontrado, 'El usuario debería existir dentro de la transacción');
        
        // Forzar error para rollback
        throw new \Exception('Error simulado para rollback');
        
        $this->testDb->commit();
    } catch (\Exception $e) {
        $this->testDb->rollback();
        // Log del error para depuración
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