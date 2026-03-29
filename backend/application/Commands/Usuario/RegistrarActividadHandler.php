<?php

declare(strict_types=1);

namespace maquinas_recreativas\Application\Commands\Usuario;

use maquinas_recreativas\Application\Commands\Command;
use maquinas_recreativas\Domain\Usuario\UsuarioRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;
use maquinas_recreativas\Application\Commands\CommandHandler;

/**
 * Manejador para el comando de registro de actividad de usuario.
 *
 * @package maquinas_recreativas\Application\Commands\Usuario
 * @version 1.0
 */
final class RegistrarActividadHandler implements CommandHandler
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
     * Maneja el comando de registro de actividad.
     *
     * @param RegistrarActividadCommand $command
     * @return void
     * @throws DomainException
     */
    public function handle(Command $command): void
    {
        // Verificar que el usuario existe
        $usuario = $this->usuarioRepository->findById($command->usuarioId);

        if (!$usuario) {
            throw new DomainException(
                "Usuario con ID '{$command->usuarioId->value()}' no encontrado.",
                'USER_NOT_FOUND'
            );
        }

        // Registrar la actividad (delegar al repositorio)
        $this->usuarioRepository->registrarActividad($command->usuarioId, $command->descripcion);
    }
}