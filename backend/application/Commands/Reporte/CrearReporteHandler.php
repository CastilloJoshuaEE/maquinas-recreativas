<?php
/**
 * application/commands/reporte/CrearReporteHandler.php
 *
 * Manejador del comando CrearReporte.
 *
 * @package maquinas_recreativas\Application\Commands\Reporte
 */

namespace maquinas_recreativas\Application\Commands\Reporte;

use maquinas_recreativas\Application\Commands\Command;
use maquinas_recreativas\Application\Commands\CommandHandler;
use maquinas_recreativas\Domain\Reporte\Reporte;
use maquinas_recreativas\Domain\Reporte\ReporteRepository;
use maquinas_recreativas\Domain\Notificacion\NotificacionReporte;
use maquinas_recreativas\Domain\Notificacion\NotificacionRepository;
use maquinas_recreativas\Domain\Usuario\UsuarioRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;

final class CrearReporteHandler implements CommandHandler
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

    public function handle(Command $command): string
    {
        if (!$command instanceof CrearReporteCommand) {
            throw new DomainException('Comando inválido');
        }

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

        if ($idUsuarioDestinatario) {
            $mensaje = "Tienes un nuevo reporte: " . substr($command->descripcion(), 0, 50) . "...";
            $notificacion = NotificacionReporte::crear($reporte->id(), $idUsuarioDestinatario, $mensaje);
            $this->notificacionRepository->saveReporte($notificacion);
        }

        return $reporte->id()->value();
    }
}