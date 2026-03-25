<?php
/**
 * application/commands/reporte/CrearReporteHandler.php
 *
 * Manejador del comando CrearReporte.
 *
 * @package Reconocimiento\Application\Commands\Reporte
 */

namespace Reconocimiento\Application\Commands\Reporte;

use Reconocimiento\Domain\Reporte\Reporte;
use Reconocimiento\Domain\Reporte\ReporteRepository;
use Reconocimiento\Domain\Notificacion\NotificacionReporte;
use Reconocimiento\Domain\Notificacion\NotificacionRepository;
use Reconocimiento\Domain\Usuario\UsuarioRepository;
use Reconocimiento\Domain\Shared\ValueObjects\Uuid;
use Reconocimiento\Domain\Shared\Exceptions\DomainException;

/**
 * Class CrearReporteHandler
 */
final class CrearReporteHandler
{
    private ReporteRepository $reporteRepository;
    private NotificacionRepository $notificacionRepository;
    private UsuarioRepository $usuarioRepository;

    public function __construct(
        ReporteRepository $reporteRepository,
        NotificacionRepository $notificacionRepository,
        UsuarioRepository $usuarioRepository
    ) {
        $this->reporteRepository = $reporteRepository;
        $this->notificacionRepository = $notificacionRepository;
        $this->usuarioRepository = $usuarioRepository;
    }

    public function handle(CrearReporte $command): string
    {
        $idUsuarioEmisor = new Uuid($command->idUsuarioEmisor());
        $emisor = $this->usuarioRepository->findById($idUsuarioEmisor);

        if (!$emisor) {
            throw new DomainException('Usuario emisor no encontrado');
        }

        $idUsuarioDestinatario = $command->idUsuarioDestinatario() ? new Uuid($command->idUsuarioDestinatario()) : null;

        if ($idUsuarioDestinatario) {
            $destinatario = $this->usuarioRepository->findById($idUsuarioDestinatario);
            if (!$destinatario) {
                throw new DomainException('Usuario destinatario no encontrado');
            }
        }

        $reporte = Reporte::crear(
            $idUsuarioEmisor,
            $idUsuarioDestinatario,
            $command->descripcion()
        );

        $this->reporteRepository->save($reporte);

        // Crear notificación si hay destinatario
        if ($idUsuarioDestinatario) {
            $mensaje = "Tienes un nuevo reporte: " . substr($command->descripcion(), 0, 50) . "...";
            $notificacion = NotificacionReporte::crear($reporte->id(), $idUsuarioDestinatario, $mensaje);
            $this->notificacionRepository->saveReporte($notificacion);
        }

        return $reporte->id()->value();
    }
}