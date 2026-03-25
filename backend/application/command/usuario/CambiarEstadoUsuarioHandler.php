<?php

declare(strict_types=1);

namespace maquinas_recreativas\Application\Command\Usuario;

use maquinas_recreativas\Domain\Usuario\UsuarioRepository;
use maquinas_recreativas\Domain\Usuario\EstadoUsuario;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;
use maquinas_recreativas\Application\Command\CommandHandler;

/**
 * Manejador para el comando de cambio de estado de un usuario.
 *
 * @package maquinas_recreativas\Application\Command\Usuario
 * @version 1.0
 */
final class CambiarEstadoUsuarioHandler implements CommandHandler
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
     * Maneja el comando de cambio de estado.
     *
     * @param CambiarEstadoUsuarioCommand $command
     * @return void
     * @throws DomainException
     */
    public function handle(CambiarEstadoUsuarioCommand $command): void
    {
        // Buscar el usuario por ID
        $usuario = $this->usuarioRepository->findById($command->usuarioId);

        if (!$usuario) {
            throw new DomainException(
                "Usuario con ID '{$command->usuarioId->value()}' no encontrado.",
                'USER_NOT_FOUND'
            );
        }

        // Crear el Value Object del nuevo estado
        $nuevoEstado = new EstadoUsuario($command->nuevoEstado);

        // Cambiar el estado en la entidad
        $usuario->cambiarEstado($nuevoEstado);

        // Guardar los cambios
        $this->usuarioRepository->save($usuario);
    }
}