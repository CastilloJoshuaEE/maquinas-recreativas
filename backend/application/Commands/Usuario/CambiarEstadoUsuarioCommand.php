<?php

declare(strict_types=1);

namespace maquinas_recreativas\Application\Commands\Usuario;

use maquinas_recreativas\Application\Commands\Command;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;

/**
 * Comando para cambiar el estado de un usuario.
 *
 * @package maquinas_recreativas\Application\Commands\Usuario
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