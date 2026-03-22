<?php

declare(strict_types=1);

namespace RecreaSys\Application\Command\Usuario;

use RecreaSys\Application\Command\Command;
use RecreaSys\Domain\Shared\ValueObjects\Uuid;

/**
 * Comando para cambiar el estado de un usuario.
 *
 * @package RecreaSys\Application\Command\Usuario
 * @version 1.0
 */
final class CambiarEstadoUsuarioCommand implements Command
{
    public Uuid $usuarioId;
    public string $nuevoEstado;

    /**
     * Constructor del comando.
     *
     * @param Uuid $usuarioId
     * @param string $nuevoEstado
     */
    public function __construct(Uuid $usuarioId, string $nuevoEstado)
    {
        $this->usuarioId = $usuarioId;
        $this->nuevoEstado = $nuevoEstado;
    }
}