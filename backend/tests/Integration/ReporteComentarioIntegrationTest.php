<?php
/**
 * Tests de integración - Flujo Reporte y Comentarios
 * 
 * @package maquinas_recreativas\Tests\Integration
 */

namespace maquinas_recreativas\Tests\Integration;

use PHPUnit\Framework\TestCase;
use maquinas_recreativas\Tests\TestDatabase;
use maquinas_recreativas\Tests\TestDatabaseInjectionTrait;
use maquinas_recreativas\Infrastructure\Repositories\MySQLUsuarioRepository;
use maquinas_recreativas\Infrastructure\Repositories\MySQLReporteRepository;
use maquinas_recreativas\Infrastructure\Repositories\MySQLComentarioRepository;
use maquinas_recreativas\Infrastructure\Repositories\MySQLNotificacionRepository;
use maquinas_recreativas\Infrastructure\Security\BcryptPasswordHasher;
use maquinas_recreativas\Application\Commands\Usuario\RegistrarUsuarioAdminCommand;
use maquinas_recreativas\Application\Commands\Usuario\RegistrarUsuarioAdminHandler;
use maquinas_recreativas\Application\Commands\Reporte\CrearReporteCommand;
use maquinas_recreativas\Application\Commands\Reporte\CrearReporteHandler;
use maquinas_recreativas\Application\Commands\Comentario\CrearComentarioCommand;
use maquinas_recreativas\Application\Commands\Comentario\CrearComentarioHandler;
use maquinas_recreativas\Application\Commands\Usuario\CambiarEstadoUsuarioCommand;
use maquinas_recreativas\Application\Commands\Usuario\CambiarEstadoUsuarioHandler;
use maquinas_recreativas\Application\Queries\Comentario\ObtenerComentariosPorReporteQuery;
use maquinas_recreativas\Application\Queries\Comentario\ObtenerComentariosPorReporteHandler;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;

class ReporteComentarioIntegrationTest extends TestCase
{
    use TestDatabaseInjectionTrait;
    
    private TestDatabase $testDb;
    private MySQLUsuarioRepository $usuarioRepository;
    private MySQLReporteRepository $reporteRepository;
    private MySQLComentarioRepository $comentarioRepository;
    private MySQLNotificacionRepository $notificacionRepository;
    private BcryptPasswordHasher $passwordHasher;
    
    private Uuid $usuario1Id;
    private Uuid $usuario2Id;
    
    protected function setUp(): void
    {
        $this->testDb = TestDatabase::getInstance();
        $this->testDb->cleanDatabase();
        
        $this->usuarioRepository = new MySQLUsuarioRepository($this->testDb);
        $this->reporteRepository = new MySQLReporteRepository($this->testDb);
        $this->comentarioRepository = new MySQLComentarioRepository($this->testDb);
        $this->notificacionRepository = new MySQLNotificacionRepository($this->testDb);
        $this->passwordHasher = new BcryptPasswordHasher();
        
        $this->crearUsuariosPrueba();
        $this->activarUsuarios();
    }
    
    private function activarUsuarios(): void
    {
        $cambiarEstadoHandler = new CambiarEstadoUsuarioHandler($this->usuarioRepository);
        
        if ($this->usuario1Id) {
            $cambiarEstadoHandler->handle(new CambiarEstadoUsuarioCommand($this->usuario1Id, 'Activo'));
        }
        if ($this->usuario2Id) {
            $cambiarEstadoHandler->handle(new CambiarEstadoUsuarioCommand($this->usuario2Id, 'Activo'));
        }
    }
    
    private function crearUsuariosPrueba(): void
    {
        $registrarAdminHandler = new RegistrarUsuarioAdminHandler($this->usuarioRepository);
        
        // Usuario 1 (Administrador)
        $command1 = new RegistrarUsuarioAdminCommand(
            'Usuario', 'Uno', '1111111111', 'usuario1@test.com', null, 'Password123!', 'Administrador', 'Activo'
        );
        $usuario1 = $registrarAdminHandler->handle($command1);
        $this->usuario1Id = $usuario1->getId();
        
        // Usuario 2 (Tecnico)
        $command2 = new RegistrarUsuarioAdminCommand(
            'Usuario', 'Dos', '2222222222', 'usuario2@test.com', null, 'Password123!', 'Tecnico', 'Activo', 'Ensamblador'
        );
        $usuario2 = $registrarAdminHandler->handle($command2);
        $this->usuario2Id = $usuario2->getId();
        
        $this->assertNotNull($this->usuario1Id);
        $this->assertNotNull($this->usuario2Id);
    }
    
    /**
     * @test
     * CPI-002: Flujo completo Reporte-Comentarios
     */
    public function testFlujoReporteYComentarios(): void
    {
        // 1. Crear reporte
        $crearReporte = new CrearReporteHandler($this->reporteRepository, $this->notificacionRepository, $this->usuarioRepository);
        
        $reporteCommand = new CrearReporteCommand(
            $this->usuario1Id->value(),
            $this->usuario2Id->value(),
            'Problema crítico con la máquina'
        );
        $reporteId = $crearReporte->handle($reporteCommand);
        
        $this->assertNotEmpty($reporteId);
        
        // Verificar que el reporte se guardó correctamente
        $reporte = $this->reporteRepository->findById(new Uuid($reporteId));
        $this->assertNotNull($reporte);
        $this->assertEquals('Problema crítico con la máquina', $reporte->descripcion());
        
        // 2. Crear comentario
        $crearComentario = new CrearComentarioHandler(
            $this->comentarioRepository,
            $this->reporteRepository,
            $this->notificacionRepository,
            $this->usuarioRepository
        );
        
        $comentarioCommand = new CrearComentarioCommand(
            $reporteId,
            $this->usuario2Id->value(),
            'Estoy trabajando en la solución, gracias por reportar'
        );
        $comentarioId = $crearComentario->handle($comentarioCommand);
        
        $this->assertNotEmpty($comentarioId);
        
        // 3. Obtener comentarios del reporte
        $obtenerComentarios = new ObtenerComentariosPorReporteHandler(
            $this->comentarioRepository,
            $this->reporteRepository,
            $this->usuarioRepository
        );
        
        $comentariosQuery = new ObtenerComentariosPorReporteQuery($reporteId, $this->usuario1Id->value());
        $comentarios = $obtenerComentarios->handle($comentariosQuery);
        
        // Verificar resultados
        $this->assertIsArray($comentarios);
        $this->assertCount(1, $comentarios);
        $this->assertEquals('Estoy trabajando en la solución, gracias por reportar', $comentarios[0]['comentario']);
        $this->assertEquals('Usuario', $comentarios[0]['nombre']);
        $this->assertEquals('Dos', $comentarios[0]['apellido']);
    }
}