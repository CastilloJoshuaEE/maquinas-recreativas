<?php

declare(strict_types=1);

namespace maquinas_recreativas\Application\Command\Usuario;

use maquinas_recreativas\Application\Command\Command;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;

/**
 * Comando para registrar una actividad de un usuario.
 *
 * @package maquinas_recreativas\Application\Command\Usuario
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