<?php
/**
 * Manejador del comando de inicio de sesión
 * 
 * @package Application\Commands\Usuario
 */

namespace maquinas_recreativas\Application\Commands\Usuario;

use maquinas_recreativas\Domain\Usuario\UsuarioRepository;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;
use maquinas_recreativas\Application\Commands\CommandHandler;
use maquinas_recreativas\Application\Commands\Command;
use InvalidArgumentException;

class LoginHandler implements CommandHandler
{
    private UsuarioRepository $usuarioRepository;
    
    public function __construct(UsuarioRepository $usuarioRepository)
    {
        $this->usuarioRepository = $usuarioRepository;
    }
    
public function handle(Command $command): array
{
    if (!$command instanceof LoginCommand) {
        throw new InvalidArgumentException('Comando inválido para este handler');
    }
    
    if (empty($command->getUsuarioAsignado()) || empty($command->getContrasena())) {
        throw new DomainException('Usuario y contraseña son requeridos');
    }
    
    $usuario = $this->usuarioRepository->searchByUsuarioAsignado($command->getUsuarioAsignado());
    
    if (!$usuario) {
        throw new DomainException('Credenciales inválidas');
    }
    
    if (!$usuario->estaActivo()) {
        throw new DomainException('Usuario no activo. Contacte al administrador');
    }
    
    if (!password_verify($command->getContrasena(), $usuario->getContrasenaHash())) {
        throw new DomainException('Credenciales inválidas');
    }
    
    // Registrar actividad
    if (method_exists($this->usuarioRepository, 'registrarActividad')) {
        $this->usuarioRepository->registrarActividad($usuario->getId(), 'Inicio de sesión');
    }
    
    // Asegurar que el tipo se devuelve como string, no como objeto
    $tipo = $usuario->getTipo();
    $tipoValue = is_object($tipo) ? $tipo->value() : $tipo;
    
    return [
        'id' => $usuario->getId()->value(),
        'nombre' => $usuario->getNombre(),
        'apellido' => $usuario->getApellido(),
        'email' => $usuario->getEmailValue(),
        'usuario_asignado' => $usuario->getUsuarioAsignado(),
        'tipo' => $tipoValue,
        'estado' => $usuario->getEstado()->value(),
        'especialidad' => $usuario->getEspecialidad()
    ];
}
}