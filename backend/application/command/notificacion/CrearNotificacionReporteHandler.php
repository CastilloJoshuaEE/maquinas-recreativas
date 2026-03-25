<?php
/**
 * application/commands/notificacion/CrearNotificacionReporteHandler.php
 *
 * Manejador del comando CrearNotificacionReporte.
 *
 * @package Reconocimiento\Application\Commands\Notificacion
 */

namespace Reconocimiento\Application\Commands\Notificacion;

use Reconocimiento\Domain\Notificacion\NotificacionReporte;
use Reconocimiento\Domain\Notificacion\NotificacionRepository;
use Reconocimiento\Domain\Reporte\ReporteRepository;
use Reconocimiento\Domain\Usuario\UsuarioRepository;
use Reconocimiento\Domain\Shared\ValueObjects\Uuid;
use Reconocimiento\Domain\Shared\Exceptions\DomainException;

/**
 * Class CrearNotificacionReporteHandler
 */
final class CrearNotificacionReporteHandler
{
    private NotificacionRepository $notificacionRepository;
    private ReporteRepository $reporteRepository;
    private UsuarioRepository $usuarioRepository;

    public function __construct(
        NotificacionRepository $notificacionRepository,
        ReporteRepository $reporteRepository,
        UsuarioRepository $usuarioRepository
    ) {
        $this->notificacionRepository = $notificacionRepository;
        $this->reporteRepository = $reporteRepository;
        $this->usuarioRepository = $usuarioRepository;
    }

    public function handle(CrearNotificacionReporte $command): void
    {
        $idReporte = new Uuid($command->idReporte());
        $idUsuario = new Uuid($command->idUsuario());

        $reporte = $this->reporteRepository->findById($idReporte);
        if (!$reporte) {
            throw new DomainException('Reporte no encontrado');
        }

        $usuario = $this->usuarioRepository->findById($idUsuario);
        if (!$usuario) {
            throw new DomainException('Usuario no encontrado');
        }

        $notificacion = NotificacionReporte::crear(
            $idReporte,
            $idUsuario,
            $command->mensaje()
        );

        $this->notificacionRepository->saveReporte($notificacion);
    }
}