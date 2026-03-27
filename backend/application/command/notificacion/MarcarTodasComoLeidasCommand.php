<?php
/**
 * application/commands/notificacion/MarcarTodasComoLeidasCommand.php
 *
 * Comando para marcar todas las notificaciones como leídas.
 *
 * @package maquinas_recreativas\Application\Commands\Notificacion
 */

namespace maquinas_recreativas\Application\Commands\Notificacion;

/**
 * Class MarcarTodasComoLeidas
 */
final class MarcarTodasComoLeidasCommand
{
    private string $idUsuario;

    public function __construct(string $idUsuario)
    {
        $this->idUsuario = $idUsuario;
    }

    public function idUsuario(): string { return $this->idUsuario; }
}