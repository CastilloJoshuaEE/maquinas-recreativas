<?php

declare(strict_types=1);

namespace maquinas_recreativas\Application\Command\Usuario;

use maquinas_recreativas\Domain\Usuario\UsuarioRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;
use maquinas_recreativas\Application\Command\CommandHandler;
use maquinas_recreativas\Infrastructure\Security\CifradoHelper;

/**
 * Manejador para el comando de actualización del nombre de usuario asignado.
 *
 * @package maquinas_recreativas\Application\Command\Usuario
 * @version 1.0
 */
final class ActualizarUsuarioAsignadoHandler implements CommandHandler
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
     * Maneja el comando de actualización de usuario asignado.
     *
     * @param ActualizarUsuarioAsignadoCommand $command
     * @return void
     * @throws DomainException
     */
    public function handle(ActualizarUsuarioAsignadoCommand $command): void
    {
        // Validar formato del email
        if (!filter_var($command->email, FILTER_VALIDATE_EMAIL)) {
            throw new DomainException(
                "El formato del email '{$command->email}' es inválido.",
                'USER_INVALID_EMAIL'
            );
        }

        // Buscar el usuario por email
        $emailEncriptado = CifradoHelper::encriptar($command->email);
        $usuario = $this->usuarioRepository->findByEmail($emailEncriptado);

        if (!$usuario) {
            throw new DomainException(
                "No se encontró un usuario con el email '{$command->email}'.",
                'USER_NOT_FOUND_BY_EMAIL'
            );
        }

        // Validar que el nuevo nombre de usuario no esté en uso por otro usuario
        if ($this->usuarioRepository->existsByUsuarioAsignadoAndNotId($command->nuevoUsuarioAsignado, $usuario->getId())) {
            throw new DomainException(
                "El nombre de usuario '{$command->nuevoUsuarioAsignado}' ya está en uso.",
                'USER_USERNAME_EXISTS'
            );
        }

        // Validar longitud mínima
        if (strlen($command->nuevoUsuarioAsignado) < 3) {
            throw new DomainException(
                "El nombre de usuario debe tener al menos 3 caracteres.",
                'USER_USERNAME_TOO_SHORT'
            );
        }

        // Actualizar el nombre de usuario en la entidad
        $usuario->actualizarUsuarioAsignado($command->nuevoUsuarioAsignado);

        // Guardar los cambios
        $this->usuarioRepository->save($usuario);
    }
}