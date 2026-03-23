<?php
/**
 * application/commands/usuario/ActualizarPerfilHandler.php
 *
 * Manejador del comando ActualizarPerfil.
 *
 * @package Reconocimiento\Application\Commands\Usuario
 */

namespace Reconocimiento\Application\Commands\Usuario;

use Reconocimiento\Domain\Usuario\Usuario;
use Reconocimiento\Domain\Usuario\UsuarioRepository;
use Reconocimiento\Domain\Shared\ValueObjects\Uuid;
use Reconocimiento\Domain\Shared\ValueObjects\Email;
use Reconocimiento\Domain\Shared\Exceptions\DomainException;

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