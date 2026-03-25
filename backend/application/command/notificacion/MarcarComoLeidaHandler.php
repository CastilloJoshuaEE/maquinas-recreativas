<?php
/**
 * application/commands/notificacion/MarcarComoLeidaHandler.php
 *
 * Manejador del comando MarcarComoLeida.
 *
 * @package maquinas_recreativas\Application\Commands\Notificacion
 */

namespace maquinas_recreativas\Application\Commands\Notificacion;

use maquinas_recreativas\Domain\Notificacion\NotificacionRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;

/**
 * Class MarcarComoLeidaHandler
 */
final class MarcarComoLeidaHandler
{
    private NotificacionRepository $notificacionRepository;

    public function __construct(NotificacionRepository $notificacionRepository)
    {
        $this->notificacionRepository = $notificacionRepository;
    }

    public function handle(MarcarComoLeida $command): void
    {
        $idNotificacion = new Uuid($command->idNotificacion());
        $idUsuario = new Uuid($command->idUsuario());

        // Primero intentamos en notificaciones de reportes
        $notificacionReporte = $this->notificacionRepository->findReporteById($idNotificacion);
        if ($notificacionReporte) {
            $this->notificacionRepository->marcarLeidaReporte($idNotificacion, $idUsuario);
            return;
        }

        // Si no es de reporte, es de máquina
        $notificacionMaquina = $this->notificacionRepository->findMaquinaById($idNotificacion);
        if ($notificacionMaquina) {
            $this->notificacionRepository->marcarLeidaMaquina($idNotificacion);
            return;
        }

        throw new DomainException('Notificación no encontrada');
    }
}