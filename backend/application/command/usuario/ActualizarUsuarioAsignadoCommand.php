<?php

declare(strict_types=1);

namespace maquinas_recreativas\Application\Command\Usuario;

use maquinas_recreativas\Application\Command\Command;

/**
 * Comando para actualizar el nombre de usuario asignado mediante email.
 *
 * @package maquinas_recreativas\Application\Command\Usuario
 * @version 1.0
 */
final class ActualizarUsuarioAsignadoCommand implements Command
{
    public string $email;
    public string $nuevoUsuarioAsignado;

    /**
     * Constructor del comando.
     *
     * @param string $email
     * @param string $nuevoUsuarioAsignado
     */
    public function __construct(string $email, string $nuevoUsuarioAsignado)
    {
        $this->email = $email;
        $this->nuevoUsuarioAsignado = $nuevoUsuarioAsignado;
    }
}