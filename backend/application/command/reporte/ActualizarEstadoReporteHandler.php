<?php
/**
 * application/commands/reporte/ActualizarEstadoReporteHandler.php
 *
 * Manejador del comando ActualizarEstadoReporte.
 *
 * @package Reconocimiento\Application\Commands\Reporte
 */

namespace Reconocimiento\Application\Commands\Reporte;

use Reconocimiento\Domain\Reporte\Reporte;
use Reconocimiento\Domain\Reporte\EstadoReporte;
use Reconocimiento\Domain\Reporte\ReporteRepository;
use Reconocimiento\Domain\Notificacion\NotificacionReporte;
use Reconocimiento\Domain\Notificacion\NotificacionRepository;
use Reconocimiento\Domain\Shared\ValueObjects\Uuid;
use Reconocimiento\Domain\Shared\Exceptions\DomainException;

/**
 * Class ActualizarEstadoReporteHandler
 */
final class ActualizarEstadoReporteHandler
{
    private ReporteRepository $reporteRepository;
    private NotificacionRepository $notificacionRepository;

    public function __construct(
        ReporteRepository $reporteRepository,
        NotificacionRepository $notificacionRepository
    ) {
        $this->reporteRepository = $reporteRepository;
        $this->notificacionRepository = $notificacionRepository;
    }

    public function handle(ActualizarEstadoReporte $command): void
    {
        $idReporte = new Uuid($command->idReporte());
        $reporte = $this->reporteRepository->findById($idReporte);

        if (!$reporte) {
            throw new DomainException('Reporte no encontrado');
        }

        $nuevoEstado = EstadoReporte::fromString($command->estado());
        $reporte->actualizarEstado($nuevoEstado);

        $this->reporteRepository->save($reporte);

        // Notificar al emisor y destinatario
        $mensaje = "El estado del reporte ha cambiado a: {$command->estado()}";

        $notificacionEmisor = NotificacionReporte::crear($idReporte, $reporte->idUsuarioEmisor(), $mensaje);
        $this->notificacionRepository->saveReporte($notificacionEmisor);

        if ($reporte->idUsuarioDestinatario()) {
            $notificacionDestinatario = NotificacionReporte::crear($idReporte, $reporte->idUsuarioDestinatario(), $mensaje);
            $this->notificacionRepository->saveReporte($notificacionDestinatario);
        }
    }
}