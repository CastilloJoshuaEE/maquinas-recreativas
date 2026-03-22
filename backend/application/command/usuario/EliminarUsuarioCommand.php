<?php

declare(strict_types=1);

namespace RecreaSys\Application\Command\Usuario;

use RecreaSys\Application\Command\Command;
use RecreaSys\Domain\Shared\ValueObjects\Uuid;

/**
 * Comando para eliminar un usuario.
 *
 * @package RecreaSys\Application\Command\Usuario
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