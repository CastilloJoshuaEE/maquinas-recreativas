<?php

declare(strict_types=1);

namespace RecreaSys\Application\Command\Usuario;

use RecreaSys\Domain\Usuario\UsuarioRepository;
use RecreaSys\Domain\Shared\ValueObjects\Uuid;
use RecreaSys\Domain\Shared\Exceptions\DomainException;
use RecreaSys\Application\Command\CommandHandler;

/**
 * Manejador para el comando de cierre de sesión (Logout).
 *
 * @package RecreaSys\Application\Command\Usuario
 * @version 1.0
 */
final class LogoutHandler implements CommandHandler
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
     * Maneja el comando de logout.
     *
     * @param LogoutCommand $command
     * @return void
     * @throws DomainException
     */
    public function handle(LogoutCommand $command): void
    {
        // Verificar que el usuario existe
        $usuario = $this->usuarioRepository->findById($command->usuarioId);

        if (!$usuario) {
            throw new DomainException(
                "Usuario con ID '{$command->usuarioId->value()}' no encontrado.",
                'USER_NOT_FOUND'
            );
        }

        // Registrar el logout (delegar al repositorio)
        $this->usuarioRepository->registrarLogout($command->usuarioId);
    }
}