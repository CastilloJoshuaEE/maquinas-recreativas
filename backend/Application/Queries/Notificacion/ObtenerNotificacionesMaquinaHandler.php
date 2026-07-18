<?php
/**
 * application/queries/notificacion/ObtenerNotificacionesMaquinaHandler.php
 *
 * Manejador del query ObtenerNotificacionesMaquina.
 *
 * @package maquinas_recreativas\Application\Queries\Notificacion
 */

namespace maquinas_recreativas\Application\Queries\Notificacion;

use maquinas_recreativas\Domain\Notificacion\NotificacionRepository;
use maquinas_recreativas\Domain\Usuario\UsuarioRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;

/**
 * Class ObtenerNotificacionesMaquinaHandler
 */
final class ObtenerNotificacionesMaquinaHandler
{
    private NotificacionRepository $notificacionRepository;
    private UsuarioRepository $usuarioRepository;

    public function __construct(
        NotificacionRepository $notificacionRepository,
        UsuarioRepository $usuarioRepository
    ) {
        $this->notificacionRepository = $notificacionRepository;
        $this->usuarioRepository = $usuarioRepository;
    }

    /**
     * Maneja el query de obtener notificaciones de máquinas.
     *
     * @param ObtenerNotificacionesMaquinaQuery $query
     * @return array
     * @throws DomainException
     */
    public function handle(ObtenerNotificacionesMaquinaQuery $query): array
    {
        $idDestinatario = new Uuid($query->getIdDestinatario());

        $usuario = $this->usuarioRepository->findById($idDestinatario);
        if (!$usuario) {
            throw new DomainException('Usuario no encontrado.');
        }

        $notificaciones = $this->notificacionRepository->findMaquinasByDestinatario($idDestinatario);

        return [
            'notificaciones' => $notificaciones,
            'count' => count($notificaciones)
        ];
    }
}