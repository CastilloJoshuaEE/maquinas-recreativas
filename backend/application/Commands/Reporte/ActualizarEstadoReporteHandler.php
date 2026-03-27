<?php
/**
 * application/commands/reporte/ActualizarEstadoReporteHandler.php
 *
 * Manejador del comando ActualizarEstadoReporte.
 *
 * @package maquinas_recreativas\Application\Commands\Reporte
 */

namespace maquinas_recreativas\Application\Commands\Reporte;

use maquinas_recreativas\Domain\Reporte\Reporte;
use maquinas_recreativas\Domain\Reporte\EstadoReporte;
use maquinas_recreativas\Domain\Reporte\ReporteRepository;
use maquinas_recreativas\Domain\Notificacion\NotificacionReporte;
use maquinas_recreativas\Domain\Notificacion\NotificacionRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;

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