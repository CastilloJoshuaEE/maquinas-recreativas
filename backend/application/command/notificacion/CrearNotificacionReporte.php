<?php
/**
 * application/commands/notificacion/CrearNotificacionReporte.php
 *
 * Comando para crear una notificación de reporte.
 *
 * @package maquinas_recreativas\Application\Commands\Notificacion
 */

namespace maquinas_recreativas\Application\Commands\Notificacion;

/**
 * Class CrearNotificacionReporte
 */
final class CrearNotificacionReporte
{
    private string $idReporte;
    private string $idUsuario;
    private string $mensaje;

    public function __construct(string $idReporte, string $idUsuario, string $mensaje)
    {
        $this->idReporte = $idReporte;
        $this->idUsuario = $idUsuario;
        $this->mensaje = $mensaje;
    }

    public function idReporte(): string { return $this->idReporte; }
    public function idUsuario(): string { return $this->idUsuario; }
    public function mensaje(): string { return $this->mensaje; }
}