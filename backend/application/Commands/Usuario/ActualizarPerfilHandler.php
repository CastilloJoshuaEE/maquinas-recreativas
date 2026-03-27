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

/**
 * Class ActualizarPerfilHandler
 */
final class ActualizarPerfilHandler
{
    private UsuarioRepository $usuarioRepository;
    public function __construct(UsuarioRepository $usuarioRepository){
        $this->usuarioRepository = $usuarioRepository;
    }    
    public function handle(ActualizarPerfilCommand $command): void{
        $id = new Uuid($command->id());
        $usuario = $this->usuarioRepository->findById($id);
        if(!$usuario){
            throw new DomainException('Usuario no encontrado');
        }
        $email = new Email($command->email());
        $usuario->actualizarPerfil(
            $command->nombre(),
            $command->apellido(),
            $email,
            $command->ci(),
            $command->tipo(),
            $command->estado(),
            $command->especialidad(),
            $command->contrasena()
        );
        $this->usuarioRepository->save($usuario);

    }
}