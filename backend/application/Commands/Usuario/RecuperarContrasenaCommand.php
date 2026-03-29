<?php

declare(strict_types=1);

namespace maquinas_recreativas\Application\Commands\Usuario;

use maquinas_recreativas\Application\Commands\Command;

/**
 * Comando para recuperar/restablecer la contraseña de un usuario.
 *
 * @package maquinas_recreativas\Application\Commands\Usuario
 * @version 1.0
 */
final class RecuperarContrasenaCommand implements Command
{
    public string $email;
    public string $nuevaContrasena;

    /**
     * Constructor del comando.
     *
     * @param string $email
     * @param string $nuevaContrasena
     */
    public function __construct(string $email, string $nuevaContrasena)
    {
        $this->email = $email;
        $this->nuevaContrasena = $nuevaContrasena;
    }
}