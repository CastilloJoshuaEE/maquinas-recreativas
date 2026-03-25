<?php
/**
 * application/queries/notificacion/ObtenerCantidadNoLeidas.php
 *
 * Query para obtener cantidad de notificaciones no leídas (reportes).
 *
 * @package maquinas_recreativas\Application\Queries\Notificacion
 */

namespace maquinas_recreativas\Application\Queries\Notificacion;

/**
 * Class ObtenerCantidadNoLeidasQuery
 */
final class ObtenerCantidadNoLeidasQuery
{
    private string $idUsuario;

    public function __construct(string $idUsuario)
    {
        $this->idUsuario = $idUsuario;
    }

    public function getIdUsuario(): string { return $this->idUsuario; }
}