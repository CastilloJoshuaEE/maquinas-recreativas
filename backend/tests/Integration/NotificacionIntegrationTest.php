<?php
/**
 * Tests de integración - Notificaciones
 * 
 * @package maquinas_recreativas\Tests\Integration
 */

namespace maquinas_recreativas\Tests\Integration;

use PHPUnit\Framework\TestCase;
use maquinas_recreativas\Tests\TestDatabase;
use maquinas_recreativas\Tests\TestDatabaseInjectionTrait;
use maquinas_recreativas\Infrastructure\Repositories\MySQLUsuarioRepository;
use maquinas_recreativas\Infrastructure\Repositories\MySQLReporteRepository;
use maquinas_recreativas\Infrastructure\Repositories\MySQLNotificacionRepository;
use maquinas_recreativas\Infrastructure\Security\BcryptPasswordHasher;
use maquinas_recreativas\Application\Commands\Usuario\RegistrarUsuarioCommand;
use maquinas_recreativas\Application\Commands\Usuario\RegistrarUsuarioHandler;
use maquinas_recreativas\Application\Commands\Reporte\CrearReporteCommand;
use maquinas_recreativas\Application\Commands\Reporte\CrearReporteHandler;
use maquinas_recreativas\Application\Commands\Notificacion\MarcarComoLeidaCommand;
use maquinas_recreativas\Application\Commands\Notificacion\MarcarComoLeidaHandler;
use maquinas_recreativas\Application\Queries\Notificacion\ObtenerNotificacionesReporteQuery;
use maquinas_recreativas\Application\Queries\Notificacion\ObtenerNotificacionesReporteHandler;
use maquinas_recreativas\Application\Queries\Notificacion\ObtenerCantidadNoLeidasQuery;
use maquinas_recreativas\Application\Queries\Notificacion\ObtenerCantidadNoLeidasHandler;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;

class NotificacionIntegrationTest extends TestCase
{
    use TestDatabaseInjectionTrait;
    
    private TestDatabase $testDb;
    private MySQLUsuarioRepository $usuarioRepository;
    private MySQLReporteRepository $reporteRepository;
    private MySQLNotificacionRepository $notificacionRepository;
    private BcryptPasswordHasher $passwordHasher;
    
    private Uuid $emisorId;
    private Uuid $destinatarioId;
    
    protected function setUp(): void
    {
        $this->testDb = TestDatabase::getInstance();
        $this->testDb->cleanDatabase();
        
        $this->usuarioRepository = new MySQLUsuarioRepository($this->testDb);
        $this->reporteRepository = new MySQLReporteRepository($this->testDb);
        $this->notificacionRepository = new MySQLNotificacionRepository($this->testDb);
        $this->passwordHasher = new BcryptPasswordHasher();
        
        $this->crearUsuariosPrueba();
    }
 private function crearUsuariosPrueba(): void
{
    $registrarUsuario = new RegistrarUsuarioHandler($this->usuarioRepository, $this->passwordHasher);
    
    // Usuario Emisor (Administrador)
    $command1 = new RegistrarUsuarioCommand(
        'Emisor', 'Test', '1111111111', 'emisor@test.com', 'password123', 'Administrador'
    );
    $this->emisorId = $registrarUsuario->handle($command1);
    
    // Usuario Destinatario (Tecnico) - ESPECIALIDAD OBLIGATORIA
    $command2 = new RegistrarUsuarioCommand(
        'Destinatario', 'Test', '2222222222', 'destinatario@test.com', 'password123', 'Tecnico', 'Ensamblador'
    );
    $this->destinatarioId = $registrarUsuario->handle($command2);
    
    $this->assertNotNull($this->emisorId);
    $this->assertNotNull($this->destinatarioId);
}
    /**
     * @test
     * CPI-004: Obtener notificaciones por usuario
     */
    public function testObtenerNotificacionesUsuario(): void
    {
        // Crear reporte (esto genera notificación automáticamente)
        $crearReporte = new CrearReporteHandler($this->reporteRepository, $this->notificacionRepository, $this->usuarioRepository);
        
        $reporteCommand = new CrearReporteCommand(
            $this->emisorId->value(),
            $this->destinatarioId->value(),
            'Problema con máquina #123'
        );
        $reporteId = $crearReporte->handle($reporteCommand);
        
        $this->assertNotEmpty($reporteId);
        
        // Obtener notificaciones del destinatario
        $obtenerNotificaciones = new ObtenerNotificacionesReporteHandler($this->notificacionRepository, $this->usuarioRepository);
        
        $notificacionesQuery = new ObtenerNotificacionesReporteQuery($this->destinatarioId->value());
        $resultado = $obtenerNotificaciones->handle($notificacionesQuery);
        
        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('notificaciones', $resultado);
        $this->assertCount(1, $resultado['notificaciones']);
        $this->assertEquals('Tienes un nuevo reporte: Problema con máquina #123...', $resultado['notificaciones'][0]['mensaje']);
    }
/**
 * @test
 * CPI-103: Marcar notificación como leída
 */
public function testMarcarNotificacionComoLeida(): void
{
    // Crear reporte (genera notificación)
    $crearReporte = new CrearReporteHandler($this->reporteRepository, $this->notificacionRepository, $this->usuarioRepository);
    
    $reporteCommand = new CrearReporteCommand(
        $this->emisorId->value(),
        $this->destinatarioId->value(),
        'Reporte de prueba'
    );
    $reporteId = $crearReporte->handle($reporteCommand);
    
    $this->assertNotEmpty($reporteId);
    
    // Esperar un momento para que se procese la notificación
    sleep(1);
    
    // Obtener notificaciones
    $obtenerNotificaciones = new ObtenerNotificacionesReporteHandler($this->notificacionRepository, $this->usuarioRepository);
    $notificacionesQuery = new ObtenerNotificacionesReporteQuery($this->destinatarioId->value());
    $resultado = $obtenerNotificaciones->handle($notificacionesQuery);
    
    // Verificar la estructura del resultado
    $this->assertIsArray($resultado);
    $this->assertArrayHasKey('notificaciones', $resultado);
    $this->assertCount(1, $resultado['notificaciones']);
    
    // Obtener el ID de la notificación - verificar la clave correcta
    $notificacion = $resultado['notificaciones'][0];
    
    // La clave podría ser 'id', 'ID_Notificaciones', o algo similar
    // Depurar para ver qué claves están disponibles
    error_log("Claves de notificación: " . implode(', ', array_keys($notificacion)));
    
$notificacionId = $notificacion['ID_Notificaciones'] ?? $notificacion['id'] ?? null;
    $this->assertNotNull($notificacionId, 'No se pudo obtener el ID de la notificación. Claves disponibles: ' . implode(', ', array_keys($notificacion)));
    
    // Marcar como leída
    $marcarLeida = new MarcarComoLeidaHandler($this->notificacionRepository);
    $marcarCommand = new MarcarComoLeidaCommand($notificacionId, $this->destinatarioId->value());
    $marcarLeida->handle($marcarCommand);
    
    // Verificar que está marcada como leída
    $notificacion = $this->notificacionRepository->findReporteById(new Uuid($notificacionId));
    $this->assertNotNull($notificacion);
    $this->assertTrue($notificacion->leida());
}
    /**
     * @test
     * CPI-104: Obtener cantidad de notificaciones no leídas
     */
 public function testObtenerCantidadNoLeidas(): void
{
    // Crear 3 reportes (3 notificaciones)
    $crearReporte = new CrearReporteHandler($this->reporteRepository, $this->notificacionRepository, $this->usuarioRepository);
    
    for ($i = 1; $i <= 3; $i++) {
        $reporteCommand = new CrearReporteCommand(
            $this->emisorId->value(),
            $this->destinatarioId->value(),
            "Reporte de prueba {$i}"
        );
        $crearReporte->handle($reporteCommand);
    }
    
    // Esperar a que se procesen las notificaciones
    sleep(1);
    
    // Obtener cantidad de no leídas
    $obtenerCantidad = new ObtenerCantidadNoLeidasHandler($this->notificacionRepository);
    $cantidadQuery = new ObtenerCantidadNoLeidasQuery($this->destinatarioId->value());
    $resultado = $obtenerCantidad->handle($cantidadQuery);
    
    $this->assertEquals(3, $resultado['cantidad']);
    
    // Obtener notificaciones para marcar una como leída
    $obtenerNotificaciones = new ObtenerNotificacionesReporteHandler($this->notificacionRepository, $this->usuarioRepository);
    $notificacionesQuery = new ObtenerNotificacionesReporteQuery($this->destinatarioId->value());
    $notificaciones = $obtenerNotificaciones->handle($notificacionesQuery);
    
    // Verificar que hay notificaciones
    $this->assertIsArray($notificaciones);
    $this->assertArrayHasKey('notificaciones', $notificaciones);
    $this->assertCount(3, $notificaciones['notificaciones']);
    
    // Obtener el ID de la primera notificación
$notificacionId = $notificaciones['notificaciones'][0]['ID_Notificaciones'] 
               ?? $notificaciones['notificaciones'][0]['id'] 
               ?? null;
$this->assertNotNull($notificacionId, 'No se pudo obtener el ID de la notificación');
    // Marcar como leída
    $marcarLeida = new MarcarComoLeidaHandler($this->notificacionRepository);
    $marcarCommand = new MarcarComoLeidaCommand($notificacionId, $this->destinatarioId->value());
    $marcarLeida->handle($marcarCommand);
    
    // Verificar nueva cantidad
    $resultado2 = $obtenerCantidad->handle($cantidadQuery);
    $this->assertEquals(2, $resultado2['cantidad']);
}
}