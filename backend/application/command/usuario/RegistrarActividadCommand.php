<?php

declare(strict_types=1);

namespace RecreaSys\Application\Command\Usuario;

use RecreaSys\Application\Command\Command;
use RecreaSys\Domain\Shared\ValueObjects\Uuid;

/**
 * Comando para registrar una actividad de un usuario.
 *
 * @package RecreaSys\Application\Command\Usuario
 * @version 1.0
 */
final class RegistrarActividadCommand implements Command
{
    public Uuid $usuarioId;
    public string $descripcion;

    /**
     * Constructor del comando.
     *
     * @param Uuid $usuarioId
     * @param string $descripcion
     */
    public function __construct(Uuid $usuarioId, string $descripcion)
    {
        $this->usuarioId = $usuarioId;
        $this->descripcion = $descripcion;
    }
}