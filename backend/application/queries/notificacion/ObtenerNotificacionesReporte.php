<?php
/**
 * application/queries/notificacion/ObtenerNotificacionesReporte.php
 *
 * Query para obtener notificaciones de reportes por usuario.
 *
 * @package maquinas_recreativas\Application\Queries\Notificacion
 */

namespace maquinas_recreativas\Application\Queries\Notificacion;

/**
 * Class ObtenerNotificacionesReporteQuery
 */
final class ObtenerNotificacionesReporteQuery
{
    private string $idUsuario;

    public function __construct(string $idUsuario)
    {
        $this->idUsuario = $idUsuario;
    }

    public function getIdUsuario(): string { return $this->idUsuario; }
}