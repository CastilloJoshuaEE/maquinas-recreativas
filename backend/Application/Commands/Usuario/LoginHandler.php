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
    
    /**
     * Maneja el comando de login
     * 
     * @param Command $command
     * @return array
     * @throws DomainException
     */
    public function handle(Command $command): array
    {
        if (!$command instanceof LoginCommand) {
            throw new InvalidArgumentException('Comando inválido para este handler');
        }
        
        //  Validación mejorada (mantiene compatibilidad)
        if (empty($command->getUsuarioAsignado()) || empty($command->getContrasena())) {
            throw new DomainException(
                'Por favor, completa todos los campos para iniciar sesión.',
                'MISSING_FIELDS'
            );
        }
        
        $usuario = $this->usuarioRepository->searchByUsuarioAsignado($command->getUsuarioAsignado());
        
        //  Mensaje más amigable pero mantiene el mismo comportamiento
        if (!$usuario) {
            throw new DomainException(
                'Usuario o contraseña incorrectos. Por favor, verifica tus datos.',
                'INVALID_CREDENTIALS'
            );
        }
        
        //  Mensaje más amigable para usuario inactivo
        if (!$usuario->estaActivo()) {
            throw new DomainException(
                'Esta cuenta está desactivada. Para reactivarla, contacta al administrador del sistema.',
                'USER_INACTIVE'
            );
        }
        
        //  Validación de contraseña con mismo comportamiento
        if (!password_verify($command->getContrasena(), $usuario->getContrasenaHash())) {
            throw new DomainException(
                'Usuario o contraseña incorrectos. Por favor, verifica tus datos.',
                'INVALID_CREDENTIALS'
            );
        }
        
        //  Registrar actividad (sin cambios)
        if (method_exists($this->usuarioRepository, 'registrarActividad')) {
            $this->usuarioRepository->registrarActividad($usuario->getId(), 'Inicio de sesión');
        }
        
        //  Asegurar que el tipo se devuelve como string (sin cambios)
        $tipo = $usuario->getTipo();
        $tipoValue = is_object($tipo) ? $tipo->value() : $tipo;
        
        //  MANTIENE la estructura original + AÑADE success y message
        return [
            // 🔥 NUEVO: Para compatibilidad con frontend
            'success' => true,
            'message' => '¡Bienvenido! Inicio de sesión exitoso.',
            
            //  MANTIENE: Estructura original completa
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