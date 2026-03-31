<?php
/**
 * application/commands/usuario/ActualizarUsuarioHandler.php
 *
 * Manejador del comando ActualizarUsuario.
 *
 * @package maquinas_recreativas\Application\Commands\Usuario
 */

namespace maquinas_recreativas\Application\Commands\Usuario;

use maquinas_recreativas\Domain\Usuario\Usuario;
use maquinas_recreativas\Domain\Usuario\UsuarioRepository;
use maquinas_recreativas\Domain\Usuario\TipoUsuario;
use maquinas_recreativas\Domain\Usuario\EstadoUsuario;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Domain\Shared\ValueObjects\Email;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;
use maquinas_recreativas\Infrastructure\Security\PasswordHasher;

/**
 * Class ActualizarUsuarioHandler
 */
final class ActualizarUsuarioHandler
{
    private UsuarioRepository $usuarioRepository;
    private PasswordHasher $passwordHasher;

    public function __construct(UsuarioRepository $usuarioRepository, PasswordHasher $passwordHasher)
    {
        $this->usuarioRepository = $usuarioRepository;
        $this->passwordHasher = $passwordHasher;
    }

    public function handle(ActualizarUsuarioCommand $command): void
    {
        $id = new Uuid($command->id());
        $usuario = $this->usuarioRepository->findById($id);
        
        if (!$usuario) {
            throw new DomainException('Usuario no encontrado');
        }

        $email = new Email($command->email());
        $tipo = new TipoUsuario($command->tipo());
        $estado = new EstadoUsuario($command->estado());
        $nuevaContrasenaHash = $command->contrasena() 
            ? $this->passwordHasher->hash($command->contrasena()) 
            : null;

        $usuario->actualizar(
            $command->nombre(),
            $command->apellido(),
            $email,
            $command->ci(),
            $tipo,
            $estado,
            $command->usuarioAsignado(),
            $command->especialidad(),
            $nuevaContrasenaHash
        );
        
        $this->usuarioRepository->save($usuario);
    }
}