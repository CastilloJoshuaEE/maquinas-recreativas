<?php
/**
 * application/commands/usuario/ActualizarPerfilHandler.php
 *
 * Manejador del comando ActualizarPerfil.
 *
 * @package maquinas_recreativas\Application\Commands\Usuario
 */

namespace maquinas_recreativas\Application\Commands\Usuario;

use maquinas_recreativas\Domain\Usuario\Usuario;
use maquinas_recreativas\Domain\Usuario\UsuarioRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Domain\Shared\ValueObjects\Email;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;
use maquinas_recreativas\Infrastructure\Security\PasswordHasher;

/**
 * Class ActualizarPerfilHandler
 */
final class ActualizarPerfilHandler
{
    private UsuarioRepository $usuarioRepository;
    private PasswordHasher $passwordHasher;

    public function __construct(UsuarioRepository $usuarioRepository, PasswordHasher $passwordHasher)
    {
        $this->usuarioRepository = $usuarioRepository;
        $this->passwordHasher = $passwordHasher;
    }    

    public function handle(ActualizarPerfilCommand $command): void
    {
        $id = new Uuid($command->id());
        $usuario = $this->usuarioRepository->findById($id);
        
        if (!$usuario) {
            throw new DomainException('Usuario no encontrado');
        }

        $email = new Email($command->email());
        $nuevaContrasenaHash = $command->contrasena() 
            ? $this->passwordHasher->hash($command->contrasena()) 
            : null;

        $usuario->actualizarPerfil(
            $command->nombre(),
            $command->apellido(),
            $email,
            $command->ci(),
            $nuevaContrasenaHash
        );
        
        $this->usuarioRepository->save($usuario);
    }
}