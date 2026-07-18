<?php
/**
 * application/queries/notificacion/ObtenerNoLeidasHandler.php
 *
 * Manejador del query ObtenerNoLeidas.
 *
 * @package maquinas_recreativas\Application\Queries\Notificacion
 */

namespace maquinas_recreativas\Application\Queries\Notificacion;

use maquinas_recreativas\Domain\Notificacion\NotificacionRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;

/**
 * Class ObtenerNoLeidasHandler
 */
final class ObtenerNoLeidasHandler
{
    private NotificacionRepository $notificacionRepository;

    public function __construct(NotificacionRepository $notificacionRepository)
    {
        $this->notificacionRepository = $notificacionRepository;
    }

    /**
     * Maneja el query de obtener cantidad de notificaciones no leídas.
     *
     * @param ObtenerNoLeidasQuery $query
     * @return array
     */
    public function handle(ObtenerNoLeidasQuery $query): array
    {
        $idDestinatario = new Uuid($query->getIdDestinatario());

        $total = $this->notificacionRepository->findNoLeidasMaquina($idDestinatario);

        return [
            'total' => $total
        ];
    }
}