<?php
/**
 * application/commands/notificacion/MarcarComoLeidaCommand.php
 *
 * Comando para marcar una notificación como leída.
 *
 * @package maquinas_recreativas\Application\Commands\Notificacion
 */

namespace maquinas_recreativas\Application\Commands\Notificacion;

/**
 * Class MarcarComoLeida
 */
final class MarcarComoLeidaCommand
{
    private string $idNotificacion;
    private string $idUsuario;

    public function __construct(string $idNotificacion, string $idUsuario)
    {
        $this->idNotificacion = $idNotificacion;
        $this->idUsuario = $idUsuario;
    }

    public function idNotificacion(): string { return $this->idNotificacion; }
    public function idUsuario(): string { return $this->idUsuario; }
}