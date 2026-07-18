<?php
/**
 * Tests de integración - Chat entre usuarios
 * 
 * @package maquinas_recreativas\Tests\Integration
 */

namespace maquinas_recreativas\Tests\Integration;

use PHPUnit\Framework\TestCase;
use maquinas_recreativas\Tests\TestDatabase;
use maquinas_recreativas\Tests\TestDatabaseInjectionTrait;
use maquinas_recreativas\Infrastructure\Repositories\PDOUsuarioRepository;
use maquinas_recreativas\Infrastructure\Repositories\PDOReporteRepository;
use maquinas_recreativas\Infrastructure\Repositories\PDOComentarioRepository;
use maquinas_recreativas\Infrastructure\Repositories\PDONotificacionRepository;
use maquinas_recreativas\Infrastructure\Security\BcryptPasswordHasher;
use maquinas_recreativas\Application\Commands\Usuario\RegistrarUsuarioCommand;
use maquinas_recreativas\Application\Commands\Usuario\RegistrarUsuarioHandler;
use maquinas_recreativas\Application\Commands\Reporte\CrearReporteCommand;
use maquinas_recreativas\Application\Commands\Reporte\CrearReporteHandler;
use maquinas_recreativas\Application\Commands\Comentario\CrearComentarioCommand;
use maquinas_recreativas\Application\Commands\Comentario\CrearComentarioHandler;
use maquinas_recreativas\Application\Commands\Usuario\RegistrarUsuarioAdminCommand;
use maquinas_recreativas\Application\Commands\Usuario\RegistrarUsuarioAdminHandler;
use maquinas_recreativas\Application\Commands\Usuario\CambiarEstadoUsuarioCommand;
use maquinas_recreativas\Application\Commands\Usuario\CambiarEstadoUsuarioHandler;
use maquinas_recreativas\Application\Queries\Reporte\ObtenerChatQuery;
use maquinas_recreativas\Application\Queries\Reporte\ObtenerChatHandler;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;

class ChatUsuarioIntegrationTest extends TestCase
{
    use TestDatabaseInjectionTrait;
    
    private TestDatabase $testDb;
    private PDOUsuarioRepository $usuarioRepository;
    private PDOReporteRepository $reporteRepository;
    private PDOComentarioRepository $comentarioRepository;
    private PDONotificacionRepository $notificacionRepository;
    private BcryptPasswordHasher $passwordHasher;
    
    private Uuid $usuario1Id;
    private Uuid $usuario2Id;
    
    protected function setUp(): void
    {
        $this->testDb = TestDatabase::getInstance();
        $this->testDb->cleanDatabase();
        
        $this->usuarioRepository = new PDOUsuarioRepository($this->testDb);
        $this->reporteRepository = new PDOReporteRepository($this->testDb);
        $this->comentarioRepository = new PDOComentarioRepository($this->testDb);
        $this->notificacionRepository = new PDONotificacionRepository($this->testDb);
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
    
    $timestamp = time();
    
    // Usuario 1 (Administrador)
    $command1 = new RegistrarUsuarioAdminCommand(
        'Usuario', 'Uno', '111111' . $timestamp, 
        'usuario1_' . $timestamp . '@test.com', 
        null, 'Password123!', 'Administrador', 'Activo'
    );
    $usuario1 = $registrarAdminHandler->handle($command1);
    $this->usuario1Id = $usuario1->getId();
    
    // Usuario 2 (Tecnico)
    $command2 = new RegistrarUsuarioAdminCommand(
        'Usuario', 'Dos', '222222' . $timestamp, 
        'usuario2_' . $timestamp . '@test.com', 
        null, 'Password123!', 'Tecnico', 'Activo', 'Ensamblador'
    );
    $usuario2 = $registrarAdminHandler->handle($command2);
    $this->usuario2Id = $usuario2->getId();
    
    $this->assertNotNull($this->usuario1Id);
    $this->assertNotNull($this->usuario2Id);
}
    /**
     * @test
     * CPI-003: Comunicación entre usuarios vía comentarios en reporte
     */
    public function testFlujoChatUsuarios(): void
    {
        // 1. Crear reporte
        $crearReporte = new CrearReporteHandler($this->reporteRepository, $this->notificacionRepository, $this->usuarioRepository);
        
        $reporteCommand = new CrearReporteCommand(
            $this->usuario1Id->value(),
            $this->usuario2Id->value(),
            'Problema con máquina #123'
        );
        $reporteId = $crearReporte->handle($reporteCommand);
        
        $this->assertNotEmpty($reporteId);
        
        // 2. Crear comentarios
        $crearComentario = new CrearComentarioHandler(
            $this->comentarioRepository,
            $this->reporteRepository,
            $this->notificacionRepository,
            $this->usuarioRepository
        );
        
        // Comentario 1: Usuario1
        $crearComentario->handle(new CrearComentarioCommand(
            $reporteId,
            $this->usuario1Id->value(),
            'Hola, tengo un problema con la máquina'
        ));
        
        sleep(1);
        
        // Comentario 2: Usuario2 (respuesta)
        $crearComentario->handle(new CrearComentarioCommand(
            $reporteId,
            $this->usuario2Id->value(),
            'Cuéntame más sobre el problema'
        ));
        
        sleep(1);
        
        // Comentario 3: Usuario1 (detalle)
        $crearComentario->handle(new CrearComentarioCommand(
            $reporteId,
            $this->usuario1Id->value(),
            'La máquina no enciende cuando conecto la placa'
        ));
        
        // 3. Obtener chat
        $obtenerChat = new ObtenerChatHandler($this->reporteRepository, $this->usuarioRepository);
        
        $chatQuery = new ObtenerChatQuery($this->usuario1Id->value(), $this->usuario2Id->value());
        $reportes = $obtenerChat->handle($chatQuery);
        
        $this->assertIsArray($reportes);
        $this->assertCount(1, $reportes);
        $this->assertEquals('Problema con máquina #123', $reportes[0]['descripcion']);
        
        // 4. Verificar comentarios
        $comentarios = $this->comentarioRepository->findByReporte(
            new Uuid($reporteId),
            $this->usuario1Id
        );
        
        $this->assertCount(3, $comentarios);
        $this->assertEquals('Hola, tengo un problema con la máquina', $comentarios[0]['comentario']);
        $this->assertEquals('Cuéntame más sobre el problema', $comentarios[1]['comentario']);
        $this->assertEquals('La máquina no enciende cuando conecto la placa', $comentarios[2]['comentario']);
    }
}