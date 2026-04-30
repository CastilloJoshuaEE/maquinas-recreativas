<?php
/**
 * application/queries/notificacion/ObtenerCantidadNoLeidasHandler.php
 *
 * Manejador del query ObtenerCantidadNoLeidas.
 *
 * @package maquinas_recreativas\Application\Queries\Notificacion
 */

namespace maquinas_recreativas\Application\Queries\Notificacion;

use maquinas_recreativas\Domain\Notificacion\NotificacionRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;

/**
 * Class ObtenerCantidadNoLeidasHandler
 */
final class ObtenerCantidadNoLeidasHandler
{
    private NotificacionRepository $notificacionRepository;

    public function __construct(NotificacionRepository $notificacionRepository)
    {
        $this->notificacionRepository = $notificacionRepository;
    }

    /**
     * Maneja el query de obtener cantidad de notificaciones no leídas.
     *
     * @param ObtenerCantidadNoLeidasQuery $query
     * @return array
     */
    public function handle(ObtenerCantidadNoLeidasQuery $query): array
    {
        $idUsuario = new Uuid($query->getIdUsuario());

        $cantidad = $this->notificacionRepository->findNoLeidasReporte($idUsuario);

        return [
            'cantidad' => $cantidad
        ];
    }
}