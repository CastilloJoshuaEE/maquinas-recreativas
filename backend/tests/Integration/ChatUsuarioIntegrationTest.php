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
use maquinas_recreativas\Infrastructure\Repositories\MySQLUsuarioRepository;
use maquinas_recreativas\Infrastructure\Repositories\MySQLReporteRepository;
use maquinas_recreativas\Infrastructure\Repositories\MySQLComentarioRepository;
use maquinas_recreativas\Infrastructure\Repositories\MySQLNotificacionRepository;
use maquinas_recreativas\Infrastructure\Security\BcryptPasswordHasher;
use maquinas_recreativas\Application\Commands\Usuario\RegistrarUsuarioCommand;
use maquinas_recreativas\Application\Commands\Usuario\RegistrarUsuarioHandler;
use maquinas_recreativas\Application\Commands\Reporte\CrearReporteCommand;
use maquinas_recreativas\Application\Commands\Reporte\CrearReporteHandler;
use maquinas_recreativas\Application\Commands\Comentario\CrearComentarioCommand;
use maquinas_recreativas\Application\Commands\Comentario\CrearComentarioHandler;
use maquinas_recreativas\Application\Queries\Reporte\ObtenerChatQuery;
use maquinas_recreativas\Application\Queries\Reporte\ObtenerChatHandler;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;

class ChatUsuarioIntegrationTest extends TestCase
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
    }
    
    private function crearUsuariosPrueba(): void
    {
        $registrarUsuario = new RegistrarUsuarioHandler($this->usuarioRepository, $this->passwordHasher);
        
        // Usuario 1 (Administrador)
        $command1 = new RegistrarUsuarioCommand(
            'Usuario', 'Uno', '1111111111', 'usuario1@test.com', 'password123', 'Administrador'
        );
        $this->usuario1Id = $registrarUsuario->handle($command1);
        
        // Usuario 2 (Logistica)
        $command2 = new RegistrarUsuarioCommand(
            'Usuario', 'Dos', '2222222222', 'usuario2@test.com', 'password123', 'Logistica'
        );
        $this->usuario2Id = $registrarUsuario->handle($command2);
        
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
    
    sleep(1); // Esperar 1 segundo
    
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
    
    // 4. Verificar comentarios - ordenados por fecha (ascendente)
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