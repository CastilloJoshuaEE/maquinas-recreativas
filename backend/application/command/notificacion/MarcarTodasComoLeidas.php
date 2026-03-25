<?php
/**
 * application/commands/notificacion/MarcarTodasComoLeidas.php
 *
 * Comando para marcar todas las notificaciones como leídas.
 *
 * @package Reconocimiento\Application\Commands\Notificacion
 */

namespace Reconocimiento\Application\Commands\Notificacion;

/**
 * Class MarcarTodasComoLeidas
 */
final class MarcarTodasComoLeidas
{
    private string $idUsuario;

    public function __construct(string $idUsuario)
    {
        $this->idUsuario = $idUsuario;
    }

    public function idUsuario(): string { return $this->idUsuario; }
}