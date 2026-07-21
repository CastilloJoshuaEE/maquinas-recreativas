<?php

declare(strict_types=1);

namespace maquinas_recreativas\Application\Commands\Usuario;

use maquinas_recreativas\Domain\Usuario\UsuarioRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;
use maquinas_recreativas\Application\Commands\CommandHandler;
use maquinas_recreativas\Application\Commands\Command;
use maquinas_recreativas\Infrastructure\Security\CifradoHelper;

/**
 * Manejador para el comando de actualización del nombre de usuario asignado.
 *
 * @package maquinas_recreativas\Application\Commands\Usuario
 * @version 1.0
 */
final class ActualizarUsuarioAsignadoHandler implements CommandHandler
{
    private UsuarioRepository $usuarioRepository;

    public function __construct(UsuarioRepository $usuarioRepository)
    {
        $this->usuarioRepository = $usuarioRepository;
    }

    public function handle(Command $command): void
    {
        if (!$command instanceof ActualizarUsuarioAsignadoCommand) {
            throw new \InvalidArgumentException('Comando inválido para este handler');
        }

        //  Validar formato del email (Mensaje mejorado pero mantiene estructura)
        if (!filter_var($command->email, FILTER_VALIDATE_EMAIL)) {
            throw new DomainException(
                "El correo electrónico no es válido. Por favor, verifica el formato. (ejemplo: usuario@correo.com)",
                'USER_INVALID_EMAIL'
            );
        }

        // Buscar el usuario por email
        $emailEncriptado = CifradoHelper::encriptar($command->email);
        $usuario = $this->usuarioRepository->searchByEmail($emailEncriptado);

        //  Usuario no encontrado (Mensaje más amigable)
        if (!$usuario) {
            throw new DomainException(
                "No encontramos una cuenta con el correo '{$command->email}'. Verifica que esté registrado o regístrate.",
                'USER_NOT_FOUND_BY_EMAIL'
            );
        }

        //  Validar que el nuevo nombre de usuario no esté en uso por otro usuario
        if ($this->usuarioRepository->existsByUsuarioAsignadoAndNotId($command->nuevoUsuarioAsignado, $usuario->getId())) {
            throw new DomainException(
                "El nombre de usuario '{$command->nuevoUsuarioAsignado}' ya está en uso. Por favor, elige otro.",
                'USER_USERNAME_EXISTS'
            );
        }

        //  Validar longitud mínima
        if (strlen($command->nuevoUsuarioAsignado) < 3) {
            throw new DomainException(
                "El nombre de usuario debe tener al menos 3 caracteres. Elige uno más largo y descriptivo.",
                'USER_USERNAME_TOO_SHORT'
            );
        }

        // Actualizar el nombre de usuario en la entidad
        $usuario->actualizarUsuarioAsignado($command->nuevoUsuarioAsignado);

        // Guardar los cambios
        $this->usuarioRepository->save($usuario);
    }
}