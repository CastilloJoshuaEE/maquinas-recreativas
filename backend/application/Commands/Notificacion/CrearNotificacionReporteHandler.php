<?php
namespace maquinas_recreativas\Application\Commands\Notificacion;

use maquinas_recreativas\Application\Commands\Command;
use maquinas_recreativas\Application\Commands\CommandHandler;
use maquinas_recreativas\Domain\Notificacion\NotificacionReporte;
use maquinas_recreativas\Domain\Notificacion\NotificacionRepository;
use maquinas_recreativas\Domain\Reporte\ReporteRepository;
use maquinas_recreativas\Domain\Usuario\UsuarioRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;

final class CrearNotificacionReporteHandler implements CommandHandler
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

    public function handle(Command $command): void
    {
        if (!$command instanceof CrearNotificacionReporteCommand) {
            throw new DomainException('Comando inválido');
        }

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