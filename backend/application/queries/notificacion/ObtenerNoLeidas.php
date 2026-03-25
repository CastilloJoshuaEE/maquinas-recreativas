<?php
/**
 * application/queries/notificacion/ObtenerNoLeidas.php
 *
 * Query para obtener cantidad de notificaciones no leídas (máquinas).
 *
 * @package maquinas_recreativas\Application\Queries\Notificacion
 */

namespace maquinas_recreativas\Application\Queries\Notificacion;

/**
 * Class ObtenerNoLeidasQuery
 */
final class ObtenerNoLeidasQuery
{
    private string $idDestinatario;

    public function __construct(string $idDestinatario)
    {
        $this->idDestinatario = $idDestinatario;
    }

    public function getIdDestinatario(): string { return $this->idDestinatario; }
}