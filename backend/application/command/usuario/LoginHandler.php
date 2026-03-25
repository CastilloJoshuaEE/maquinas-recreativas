<?php
/**
 * Manejador del comando de inicio de sesión
 * 
 * @package Application\Commands\Usuario
 * @author Tu Nombre
 * @version 1.0.0
 */

namespace maquinas_recreativas\Application\Commands\Usuario;

use maquinas_recreativas\Domain\Usuario\UsuarioRepository;
use maquinas_recreativas\Domain\Usuario\Usuario;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;
use maquinas_recreativas\Infrastructure\Security\CifradoHelper;

/**
 * @package Application\Commands\Usuario
 * 
 * Manejador responsable de autenticar usuarios
 * y gestionar el inicio de sesión.
 */
class LoginHandler {
    
    /**
     * @var UsuarioRepository Repositorio de usuarios
     */
    private $usuarioRepository;
    
    /**
     * Constructor del manejador
     * 
     * @param UsuarioRepository $usuarioRepository
     */
    public function __construct(UsuarioRepository $usuarioRepository) {
        $this->usuarioRepository = $usuarioRepository;
    }
    
    /**
     * Maneja el comando de login
     * 
     * @param LoginCommand $command Comando con datos de login
     * @return array Datos del usuario autenticado
     * @throws DomainException Si las credenciales son inválidas
     */
    public function handle(LoginCommand $command): array {
        
        // Validar entrada
        if (empty($command->getUsuarioAsignado()) || empty($command->getContrasena())) {
            throw new DomainException(
                'Usuario y contraseña son requeridos',
                DomainException::HTTP_BAD_REQUEST
            );
        }
        
        // Buscar usuario por nombre de usuario
        $usuario = $this->usuarioRepository->findByUsuarioAsignado(
            $command->getUsuarioAsignado()
        );
        
        if (!$usuario) {
            throw new DomainException(
                'Credenciales inválidas',
                DomainException::HTTP_UNAUTHORIZED
            );
        }
        
        // Verificar estado del usuario
        if (!$usuario->isActivo()) {
            throw new DomainException(
                'Usuario no activo. Contacte al administrador',
                DomainException::HTTP_FORBIDDEN
            );
        }
        
        // Verificar contraseña
        if (!password_verify($command->getContrasena(), $usuario->getContrasena())) {
            throw new DomainException(
                'Credenciales inválidas',
                DomainException::HTTP_UNAUTHORIZED
            );
        }
        
        // Registrar inicio de sesión
        $this->usuarioRepository->registrarInicioSesion(
            $usuario->getId(),
            $command->getIpAddress()
        );
        
        // Registrar actividad
        $this->usuarioRepository->registrarActividad(
            $usuario->getId(),
            'Inicio de sesión',
            ['ip' => $command->getIpAddress(), 'user_agent' => $command->getUserAgent()]
        );
        
        // Retornar datos del usuario (sin contraseña)
        return [
            'id' => $usuario->getId(),
            'nombre' => $usuario->getNombre(),
            'apellido' => $usuario->getApellido(),
            'email' => $usuario->getEmail(),
            'usuario_asignado' => $usuario->getUsuarioAsignado(),
            'tipo' => $usuario->getTipo(),
            'estado' => $usuario->getEstado(),
            'especialidad' => $usuario->getEspecialidad()
        ];
    }
}