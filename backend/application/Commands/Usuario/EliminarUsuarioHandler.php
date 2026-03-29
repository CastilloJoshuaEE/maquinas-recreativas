<?php

declare(strict_types=1);

namespace maquinas_recreativas\Application\Commands\Usuario;

use maquinas_recreativas\Domain\Usuario\UsuarioRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;
use maquinas_recreativas\Application\Commands\CommandHandler;
use maquinas_recreativas\Application\Commands\Command;

/**
 * Manejador para el comando de eliminación de un usuario.
 *
 * @package maquinas_recreativas\Application\Commands\Usuario
 * @version 1.0
 */
final class EliminarUsuarioHandler implements CommandHandler
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
     * Maneja el comando de eliminación de usuario.
     *
     * @param EliminarUsuarioCommand $command
     * @return void
     * @throws DomainException
     */
    public function handle(Command $command): void
    {
        // Verificar si el usuario existe
        $usuario = $this->usuarioRepository->findById($command->usuarioId);

        if (!$usuario) {
            throw new DomainException(
                "Usuario con ID '{$command->usuarioId->value()}' no encontrado.",
                'USER_NOT_FOUND'
            );
        }

        // Verificar que no sea un administrador (regla de negocio)
        if ($usuario->getTipo()->value() === 'Administrador') {
            throw new DomainException(
                "No se puede eliminar a un administrador del sistema.",
                'USER_CANNOT_DELETE_ADMIN'
            );
        }

        // Verificar si tiene máquinas asignadas (delegar al repositorio)
        if ($this->usuarioRepository->hasMachinesAssigned($command->usuarioId)) {
            throw new DomainException(
                "No se puede eliminar el usuario porque tiene máquinas asignadas.",
                'USER_HAS_MACHINES'
            );
        }

        // Eliminar el usuario
        $this->usuarioRepository->delete($command->usuarioId);
    }
}