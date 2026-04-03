<?php
/**
 * Tests de comandos de usuario
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
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;

class UsuarioCommandTest extends TestCase
{
    private TestDatabase $testDb;
    private MySQLUsuarioRepository $usuarioRepository;
    private BcryptPasswordHasher $passwordHasher;
    
    protected function setUp(): void
    {
        $this->testDb = TestDatabase::getInstance();
        $this->testDb->cleanDatabase(); // Limpiar antes de cada test
        
        $this->usuarioRepository = new MySQLUsuarioRepository($this->testDb);
        $this->passwordHasher = new BcryptPasswordHasher();
    }
    
    /**
     * Generar CI único para evitar duplicados
     */
    private function generarCiUnico(): string
    {
        return time() . rand(1000, 9999);
    }
    
    /**
     * Generar email único
     */
    private function generarEmailUnico(string $base = 'test'): string
    {
        return $base . '_' . time() . '_' . rand(1000, 9999) . '@test.com';
    }
    
    /**
     * @test
     */
    public function testRegistrarUsuarioValido(): void
    {
        $handler = new RegistrarUsuarioHandler($this->usuarioRepository, $this->passwordHasher);
        
        $command = new RegistrarUsuarioCommand(
            'Juan',
            'Perez',
            $this->generarCiUnico(),
            $this->generarEmailUnico('juan'),
            'password123',
            'Usuario'
        );
        
        $userId = $handler->handle($command);
        
        $this->assertInstanceOf(Uuid::class, $userId);
        
        $usuario = $this->usuarioRepository->findById($userId);
        $this->assertNotNull($usuario);
        $this->assertEquals('Juan', $usuario->getNombre());
    }
    
    /**
     * @test
     */
    public function testRegistrarUsuarioEmailDuplicado(): void
    {
        $handler = new RegistrarUsuarioHandler($this->usuarioRepository, $this->passwordHasher);
        
        $email = $this->generarEmailUnico('duplicado');
        $ci = $this->generarCiUnico();
        
        $command1 = new RegistrarUsuarioCommand(
            'Juan', 'Perez', $ci, $email, 'password123', 'Usuario'
        );
        $handler->handle($command1);
        
        $ci2 = $this->generarCiUnico();
        $command2 = new RegistrarUsuarioCommand(
            'Juan2', 'Perez2', $ci2, $email, 'password123', 'Usuario'
        );
        
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('El correo electrónico ya está registrado');
        
        $handler->handle($command2);
    }
    
    /**
     * @test
     */
    public function testLoginValido(): void
    {
        $registrarHandler = new RegistrarUsuarioHandler($this->usuarioRepository, $this->passwordHasher);
        
        $ci = $this->generarCiUnico();
        $email = $this->generarEmailUnico('login');
        
        $registrarCommand = new RegistrarUsuarioCommand(
            'Juan', 'Perez', $ci, $email, 'password123', 'Usuario'
        );
        $registrarHandler->handle($registrarCommand);
        
        $loginHandler = new LoginHandler($this->usuarioRepository);
        $loginCommand = new LoginCommand('juanperez', 'password123');
        
        $resultado = $loginHandler->handle($loginCommand);
        
        $this->assertIsArray($resultado);
        $this->assertEquals('Juan', $resultado['nombre']);
        $this->assertEquals('Perez', $resultado['apellido']);
    }
    
    /**
     * @test
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
     */
    public function testLoginContrasenaIncorrecta(): void
    {
        $registrarHandler = new RegistrarUsuarioHandler($this->usuarioRepository, $this->passwordHasher);
        
        $ci = $this->generarCiUnico();
        $email = $this->generarEmailUnico('loginpass');
        
        $registrarCommand = new RegistrarUsuarioCommand(
            'Juan', 'Perez', $ci, $email, 'password123', 'Usuario'
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
     */
    public function testRecuperarContrasena(): void
    {
        $registrarHandler = new RegistrarUsuarioHandler($this->usuarioRepository, $this->passwordHasher);
        
        $ci = $this->generarCiUnico();
        $email = $this->generarEmailUnico('recuperar');
        
        $registrarCommand = new RegistrarUsuarioCommand(
            'Juan', 'Perez', $ci, $email, 'password123', 'Usuario'
        );
        $registrarHandler->handle($registrarCommand);
        
        $recuperarHandler = new RecuperarContrasenaHandler($this->usuarioRepository);
        $recuperarCommand = new RecuperarContrasenaCommand($email, 'nuevapassword456');
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
     */
    public function testRegistrarUsuarioAdmin(): void
    {
        $handler = new RegistrarUsuarioAdminHandler($this->usuarioRepository);
        
        // Usar email y CI únicos para evitar conflicto con admin existente
        $ci = $this->generarCiUnico();
        $email = $this->generarEmailUnico('admin_new');
        
        $command = new RegistrarUsuarioAdminCommand(
            'AdminNuevo',
            'Test',
            $ci,
            $email,
            'admin_nuevo_user',
            'admin123',
            'Administrador',
            'Activo'
        );
        
        $usuario = $handler->handle($command);
        
        $this->assertNotNull($usuario);
        $this->assertEquals('AdminNuevo', $usuario->getNombre());
        $this->assertEquals('Administrador', $usuario->getTipo()->value());
        $this->assertTrue($usuario->estaActivo());
    }
    
    /**
     * @test
     */
    public function testCambiarEstadoUsuario(): void
    {
        $registrarHandler = new RegistrarUsuarioHandler($this->usuarioRepository, $this->passwordHasher);
        
        $ci = $this->generarCiUnico();
        $email = $this->generarEmailUnico('cambiarestado');
        
        $registrarCommand = new RegistrarUsuarioCommand(
            'Juan', 'Perez', $ci, $email, 'password123', 'Usuario'
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
     */
    public function testActualizarPerfil(): void
    {
        $registrarHandler = new RegistrarUsuarioHandler($this->usuarioRepository, $this->passwordHasher);
        
        $ci = $this->generarCiUnico();
        $email = $this->generarEmailUnico('actualizarperfil');
        
        $registrarCommand = new RegistrarUsuarioCommand(
            'Juan', 'Perez', $ci, $email, 'password123', 'Usuario'
        );
        $userId = $registrarHandler->handle($registrarCommand);
        
        $actualizarHandler = new ActualizarPerfilHandler($this->usuarioRepository, $this->passwordHasher);
        
        $actualizarCommand = new ActualizarPerfilCommand(
            $userId->value(),
            'Juan Carlos',
            'Perez Gomez',
            $this->generarEmailUnico('nuevoemail'),
            $this->generarCiUnico(),
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