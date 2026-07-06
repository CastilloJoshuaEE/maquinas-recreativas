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
use maquinas_recreativas\Infrastructure\Repositories\PDOUsuarioRepository;
use maquinas_recreativas\Infrastructure\Repositories\PDOReporteRepository;
use maquinas_recreativas\Infrastructure\Repositories\PDONotificacionRepository;
use maquinas_recreativas\Infrastructure\Security\BcryptPasswordHasher;
use maquinas_recreativas\Application\Commands\Usuario\RegistrarUsuarioAdminCommand;
use maquinas_recreativas\Application\Commands\Usuario\RegistrarUsuarioAdminHandler;
use maquinas_recreativas\Application\Commands\Usuario\CambiarEstadoUsuarioCommand;
use maquinas_recreativas\Application\Commands\Usuario\CambiarEstadoUsuarioHandler;
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
    private PDOUsuarioRepository $usuarioRepository;
    private PDOReporteRepository $reporteRepository;
    private PDONotificacionRepository $notificacionRepository;
    private BcryptPasswordHasher $passwordHasher;
    
    private Uuid $emisorId;
    private Uuid $destinatarioId;
    
    protected function setUp(): void
    {
        $this->testDb = TestDatabase::getInstance();
        $this->testDb->cleanDatabase();
        
        $this->usuarioRepository = new PDOUsuarioRepository($this->testDb);
        $this->reporteRepository = new PDOReporteRepository($this->testDb);
        $this->notificacionRepository = new PDONotificacionRepository($this->testDb);
        $this->passwordHasher = new BcryptPasswordHasher();
        
        $this->crearUsuariosPrueba();
        $this->activarUsuarios();
    }
    
    private function activarUsuarios(): void
    {
        $cambiarEstadoHandler = new CambiarEstadoUsuarioHandler($this->usuarioRepository);
        
        if ($this->emisorId) {
            $cambiarEstadoHandler->handle(new CambiarEstadoUsuarioCommand($this->emisorId, 'Activo'));
        }
        if ($this->destinatarioId) {
            $cambiarEstadoHandler->handle(new CambiarEstadoUsuarioCommand($this->destinatarioId, 'Activo'));
        }
    }
    
private function crearUsuariosPrueba(): void
{
    $registrarAdminHandler = new RegistrarUsuarioAdminHandler($this->usuarioRepository);
    
    $timestamp = time();
    
    // Usuario Emisor (Administrador)
    $command1 = new RegistrarUsuarioAdminCommand(
        'Emisor', 'Test', '111111' . $timestamp, 
        'emisor_' . $timestamp . '@test.com', 
        null, 'Password123!', 'Administrador', 'Activo'
    );
    $usuario1 = $registrarAdminHandler->handle($command1);
    $this->emisorId = $usuario1->getId();
    
    // Usuario Destinatario (Tecnico)
    $command2 = new RegistrarUsuarioAdminCommand(
        'Destinatario', 'Test', '222222' . $timestamp, 
        'destinatario_' . $timestamp . '@test.com', 
        null, 'Password123!', 'Tecnico', 'Activo', 'Ensamblador'
    );
    $usuario2 = $registrarAdminHandler->handle($command2);
    $this->destinatarioId = $usuario2->getId();
    
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
        
        // Obtener el ID de la notificación
        $notificacion = $resultado['notificaciones'][0];
        $notificacionId = $notificacion['ID_Notificaciones'] ?? $notificacion['id'] ?? null;
        $this->assertNotNull($notificacionId, 'No se pudo obtener el ID de la notificación');
        
        // Marcar como leída
        $marcarLeida = new MarcarComoLeidaHandler($this->notificacionRepository);
        $marcarCommand = new MarcarComoLeidaCommand($notificacionId, $this->destinatarioId->value());
        $marcarLeida->handle($marcarCommand);
        
        // Verificar que está marcada como leída
        $notificacionObj = $this->notificacionRepository->findReporteById(new Uuid($notificacionId));
        $this->assertNotNull($notificacionObj);
        $this->assertTrue($notificacionObj->leida());
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
        $notificacionId = $notificaciones['notificaciones'][0]['ID_Notificaciones'] ?? $notificaciones['notificaciones'][0]['id'] ?? null;
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