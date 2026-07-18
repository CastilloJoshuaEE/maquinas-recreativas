<?php

declare(strict_types=1);

namespace maquinas_recreativas\Application\Commands\Usuario;

use maquinas_recreativas\Application\Commands\Command;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;

/**
 * Comando para eliminar un usuario.
 *
 * @package maquinas_recreativas\Application\Commands\Usuario
 * @version 1.0
 */
final class EliminarUsuarioCommand implements Command
{
    public Uuid $usuarioId;

    /**
     * Constructor del comando.
     *
     * @param Uuid $usuarioId
     */
    public function __construct(Uuid $usuarioId)
    {
        $this->usuarioId = $usuarioId;
    }
}