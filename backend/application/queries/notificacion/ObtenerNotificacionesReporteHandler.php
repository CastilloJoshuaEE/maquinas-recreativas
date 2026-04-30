<?php
/**
 * application/queries/notificacion/ObtenerNotificacionesReporteHandler.php
 *
 * Manejador del query ObtenerNotificacionesReporte.
 *
 * @package maquinas_recreativas\Application\Queries\Notificacion
 */

namespace maquinas_recreativas\Application\Queries\Notificacion;

use maquinas_recreativas\Domain\Notificacion\NotificacionRepository;
use maquinas_recreativas\Domain\Usuario\UsuarioRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;

/**
 * Class ObtenerNotificacionesReporteHandler
 */
final class ObtenerNotificacionesReporteHandler
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
     * Maneja el query de obtener notificaciones de reportes.
     *
     * @param ObtenerNotificacionesReporteQuery $query
     * @return array
     * @throws DomainException
     */
    public function handle(ObtenerNotificacionesReporteQuery $query): array
    {
        $idUsuario = new Uuid($query->getIdUsuario());

        $usuario = $this->usuarioRepository->findById($idUsuario);
        if (!$usuario) {
            throw new DomainException('Usuario no encontrado.');
        }

        $notificaciones = $this->notificacionRepository->findReportesByUsuario($idUsuario);

        return [
            'notificaciones' => $notificaciones,
            'count' => count($notificaciones)
        ];
    }
}