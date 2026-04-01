<?php
/**
 * Tests de comandos de usuario
 * 
 * @package maquinas_recreativas\Tests\Application\Commands
 */

namespace maquinas_recreativas\Tests\Application\Commands;

use PHPUnit\Framework\TestCase;
use maquinas_recreativas\Tests\TestDatabase;
use maquinas_recreativas\Infrastructure\Repositories\MySQLUsuarioRepository;
use maquinas_recreativas\Infrastructure\Security\BcryptPasswordHasher;
use maquinas_recreativas\Application\Commands\Usuario\LoginCommand;
use maquinas_recreativas\Application\Commands\Usuario\LoginHandler;
use maquinas_recreativas\Application\Commands\Usuario\RegistrarUsuarioCommand;
use maquinas_recreativas\Application\Commands\Usuario\RegistrarUsuarioHandler;
use maquinas_recreativas\Application\Commands\Usuario\RegistrarUsuarioAdminCommand;
use maquinas_recreativas\Application\Commands\Usuario\RegistrarUsuarioAdminHandler;
use maquinas_recreativas\Application\Commands\Usuario\ActualizarPerfilCommand;
use maquinas_recreativas\Application\Commands\Usuario\ActualizarPerfilHandler;
use maquinas_recreativas\Application\Commands\Usuario\RecuperarContrasenaCommand;
use maquinas_recreativas\Application\Commands\Usuario\RecuperarContrasenaHandler;
use maquinas_recreativas\Application\Commands\Usuario\CambiarEstadoUsuarioCommand;
use maquinas_recreativas\Application\Commands\Usuario\CambiarEstadoUsuarioHandler;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;

class UsuarioCommandTest extends TestCase
{
    private TestDatabase $testDb;
    private MySQLUsuarioRepository $usuarioRepository;
    private BcryptPasswordHasher $passwordHasher;
    
    protected function setUp(): void
    {
        $this->testDb = TestDatabase::getInstance();
        $this->testDb->cleanDatabase();
        
        $this->usuarioRepository = new MySQLUsuarioRepository($this->testDb);
        $this->passwordHasher = new BcryptPasswordHasher();
    }
    
    /**
     * @test
     * CP-029 - Registrar usuario válido
     */
    public function testRegistrarUsuarioValido(): void
    {
        $handler = new RegistrarUsuarioHandler($this->usuarioRepository, $this->passwordHasher);
        
        $command = new RegistrarUsuarioCommand(
            'Juan',
            'Perez',
            '1234567890',
            'juan@test.com',
            'password123',
            'Usuario'
        );
        
        $userId = $handler->handle($command);
        
        $this->assertInstanceOf(\maquinas_recreativas\Domain\Shared\ValueObjects\Uuid::class, $userId);
        
        $usuario = $this->usuarioRepository->findById($userId);
        $this->assertNotNull($usuario);
        $this->assertEquals('Juan', $usuario->getNombre());
        $this->assertEquals('Perez', $usuario->getApellido());
    }
    
    /**
     * @test
     * CP-030 - Registrar usuario con email duplicado
     */
    public function testRegistrarUsuarioEmailDuplicado(): void
    {
        $handler = new RegistrarUsuarioHandler($this->usuarioRepository, $this->passwordHasher);
        
        $command1 = new RegistrarUsuarioCommand(
            'Juan',
            'Perez',
            '1234567890',
            'juan@test.com',
            'password123',
            'Usuario'
        );
        
        $handler->handle($command1);
        
        $command2 = new RegistrarUsuarioCommand(
            'Juan2',
            'Perez2',
            '0987654321',
            'juan@test.com',
            'password123',
            'Usuario'
        );
        
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('El correo electrónico ya está registrado');
        
        $handler->handle($command2);
    }
    
    /**
     * @test
     * CP-031 - Login válido
     */
    public function testLoginValido(): void
    {
        // Primero registrar usuario
        $registrarHandler = new RegistrarUsuarioHandler($this->usuarioRepository, $this->passwordHasher);
        
        $registrarCommand = new RegistrarUsuarioCommand(
            'Juan',
            'Perez',
            '1234567890',
            'juan@test.com',
            'password123',
            'Usuario'
        );
        
        $registrarHandler->handle($registrarCommand);
        
        // Luego hacer login
        $loginHandler = new LoginHandler($this->usuarioRepository);
        
        $loginCommand = new LoginCommand('juanperez', 'password123');
        
        $resultado = $loginHandler->handle($loginCommand);
        
        $this->assertIsArray($resultado);
        $this->assertEquals('Juan', $resultado['nombre']);
        $this->assertEquals('Perez', $resultado['apellido']);
        $this->assertEquals('juanperez', $resultado['usuario_asignado']);
    }
    
    /**
     * @test
     * CP-032 - Login con usuario incorrecto
     */
    public function testLoginUsuarioIncorrecto(): void
    {
        $loginHandler = new LoginHandler($this->usuarioRepository);
        
        $loginCommand = new LoginCommand('usuariofalso', 'password123');
        
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Credenciales inválidas');
        
        $loginHandler->handle($loginCommand);
    }
    
    /**
     * @test
     * CP-033 - Login con contraseña incorrecta
     */
    public function testLoginContrasenaIncorrecta(): void
    {
        $registrarHandler = new RegistrarUsuarioHandler($this->usuarioRepository, $this->passwordHasher);
        
        $registrarCommand = new RegistrarUsuarioCommand(
            'Juan',
            'Perez',
            '1234567890',
            'juan@test.com',
            'password123',
            'Usuario'
        );
        
        $registrarHandler->handle($registrarCommand);
        
        $loginHandler = new LoginHandler($this->usuarioRepository);
        
        $loginCommand = new LoginCommand('juanperez', 'contraseñaincorrecta');
        
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Credenciales inválidas');
        
        $loginHandler->handle($loginCommand);
    }
    
    /**
     * @test
     * CP-034 - Recuperar contraseña
     */
    public function testRecuperarContrasena(): void
    {
        $registrarHandler = new RegistrarUsuarioHandler($this->usuarioRepository, $this->passwordHasher);
        
        $registrarCommand = new RegistrarUsuarioCommand(
            'Juan',
            'Perez',
            '1234567890',
            'juan@test.com',
            'password123',
            'Usuario'
        );
        
        $registrarHandler->handle($registrarCommand);
        
        $recuperarHandler = new RecuperarContrasenaHandler($this->usuarioRepository);
        
        $recuperarCommand = new RecuperarContrasenaCommand('juan@test.com', 'nuevapassword456');
        
        $recuperarHandler->handle($recuperarCommand);
        
        // Verificar login con nueva contraseña
        $loginHandler = new LoginHandler($this->usuarioRepository);
        
        $loginCommand = new LoginCommand('juanperez', 'nuevapassword456');
        
        $resultado = $loginHandler->handle($loginCommand);
        
        $this->assertIsArray($resultado);
        $this->assertEquals('Juan', $resultado['nombre']);
    }
    
    /**
     * @test
     * CP-035 - Registrar usuario administrador
     */
    public function testRegistrarUsuarioAdmin(): void
    {
        $handler = new RegistrarUsuarioAdminHandler($this->usuarioRepository);
        
        $command = new RegistrarUsuarioAdminCommand(
            'Admin',
            'Test',
            '1111111111',
            'admin@test.com',
            'admin_user',
            'admin123',
            'Administrador',
            'Activo'
        );
        
        $usuario = $handler->handle($command);
        
        $this->assertNotNull($usuario);
        $this->assertEquals('Admin', $usuario->getNombre());
        $this->assertEquals('Administrador', $usuario->getTipo()->value());
        $this->assertTrue($usuario->estaActivo());
    }
    
    /**
     * @test
     * CP-036 - Cambiar estado de usuario
     */
    public function testCambiarEstadoUsuario(): void
    {
        $registrarHandler = new RegistrarUsuarioHandler($this->usuarioRepository, $this->passwordHasher);
        
        $registrarCommand = new RegistrarUsuarioCommand(
            'Juan',
            'Perez',
            '1234567890',
            'juan@test.com',
            'password123',
            'Usuario'
        );
        
        $userId = $registrarHandler->handle($registrarCommand);
        
        $cambiarEstadoHandler = new CambiarEstadoUsuarioHandler($this->usuarioRepository);
        
        $cambiarEstadoCommand = new CambiarEstadoUsuarioCommand($userId, 'Inactivo');
        
        $cambiarEstadoHandler->handle($cambiarEstadoCommand);
        
        $usuario = $this->usuarioRepository->findById($userId);
        $this->assertFalse($usuario->estaActivo());
    }
    
    /**
     * @test
     * CP-037 - Actualizar perfil de usuario
     */
    public function testActualizarPerfil(): void
    {
        $registrarHandler = new RegistrarUsuarioHandler($this->usuarioRepository, $this->passwordHasher);
        
        $registrarCommand = new RegistrarUsuarioCommand(
            'Juan',
            'Perez',
            '1234567890',
            'juan@test.com',
            'password123',
            'Usuario'
        );
        
        $userId = $registrarHandler->handle($registrarCommand);
        
        $actualizarHandler = new ActualizarPerfilHandler($this->usuarioRepository, $this->passwordHasher);
        
        $actualizarCommand = new ActualizarPerfilCommand(
            $userId->value(),
            'Juan Carlos',
            'Perez Gomez',
            'juan.nuevo@test.com',
            '0987654321',
            'Usuario',
            'Activo',
            null,
            'nuevapassword'
        );
        
        $actualizarHandler->handle($actualizarCommand);
        
        $usuario = $this->usuarioRepository->findById($userId);
        $this->assertEquals('Juan Carlos', $usuario->getNombre());
        $this->assertEquals('Perez Gomez', $usuario->getApellido());
    }
}