<?php

declare(strict_types=1);

namespace RecreaSys\Application\Command\Usuario;

use RecreaSys\Domain\Usuario\UsuarioRepository;
use RecreaSys\Domain\Shared\Exceptions\DomainException;
use RecreaSys\Application\Command\CommandHandler;
use RecreaSys\Infrastructure\Security\CifradoHelper;

/**
 * Manejador para el comando de recuperación de contraseña.
 *
 * @package RecreaSys\Application\Command\Usuario
 * @version 1.0
 */
final class RecuperarContrasenaHandler implements CommandHandler
{
    private UsuarioRepository $usuarioRepository;

    /**
     * Constructor del handler.
     *
     * @param UsuarioRepository $usuarioRepository
     */
    public function __construct(UsuarioRepository $usuarioRepository)
    {
        $this->usuarioRepository = $usuarioRepository;
    }

    /**
     * Maneja el comando de recuperación de contraseña.
     *
     * @param RecuperarContrasenaCommand $command
     * @return void
     * @throws DomainException
     */
    public function handle(RecuperarContrasenaCommand $command): void
    {
        // Validar la nueva contraseña
        if (strlen($command->nuevaContrasena) < 8) {
            throw new DomainException(
                "La nueva contraseña debe tener al menos 8 caracteres.",
                'USER_PASSWORD_TOO_SHORT'
            );
        }

        // Validar formato del email
        if (!filter_var($command->email, FILTER_VALIDATE_EMAIL)) {
            throw new DomainException(
                "El formato del email '{$command->email}' es inválido.",
                'USER_INVALID_EMAIL'
            );
        }

        // Buscar usuario por email
        $emailEncriptado = CifradoHelper::encriptar($command->email);
        $usuario = $this->usuarioRepository->findByEmail($emailEncriptado);

        if (!$usuario) {
            throw new DomainException(
                "No se encontró un usuario con el email '{$command->email}'.",
                'USER_NOT_FOUND_BY_EMAIL'
            );
        }

        // Hashear la nueva contraseña
        $nuevoHash = password_hash($command->nuevaContrasena, PASSWORD_BCRYPT);

        // Actualizar la contraseña en la entidad
        $usuario->cambiarContrasena($nuevoHash);

        // Guardar los cambios
        $this->usuarioRepository->save($usuario);
    }
}