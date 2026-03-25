<?php
/**
 * application/queries/notificacion/ObtenerNotificacionesMaquina.php
 *
 * Query para obtener notificaciones de máquinas por usuario.
 *
 * @package maquinas_recreativas\Application\Queries\Notificacion
 */

namespace maquinas_recreativas\Application\Queries\Notificacion;

/**
 * Class ObtenerNotificacionesMaquinaQuery
 */
final class ObtenerNotificacionesMaquinaQuery
{
    private string $idDestinatario;

    public function __construct(string $idDestinatario)
    {
        $this->idDestinatario = $idDestinatario;
    }

    public function getIdDestinatario(): string { return $this->idDestinatario; }
}