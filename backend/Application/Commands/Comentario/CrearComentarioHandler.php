<?php
/**
 * application/commands/comentario/CrearComentarioHandler.php
 *
 * Manejador del comando CrearComentarioCommand.
 *
 * @package maquinas_recreativas\Application\Commands\Comentario
 */

namespace maquinas_recreativas\Application\Commands\Comentario;

use maquinas_recreativas\Domain\Comentario\Comentario;
use maquinas_recreativas\Domain\Comentario\ComentarioRepository;
use maquinas_recreativas\Domain\Reporte\ReporteRepository;
use maquinas_recreativas\Domain\Notificacion\NotificacionReporte;
use maquinas_recreativas\Domain\Notificacion\NotificacionRepository;
use maquinas_recreativas\Domain\Usuario\UsuarioRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;
use maquinas_recreativas\Application\Commands\Command;
use maquinas_recreativas\Application\Commands\CommandHandler;

/**
 * Class CrearComentarioHandler
 */
final class CrearComentarioHandler implements CommandHandler
{
    private ComentarioRepository $comentarioRepository;
    private ReporteRepository $reporteRepository;
    private NotificacionRepository $notificacionRepository;
    private UsuarioRepository $usuarioRepository;

    public function __construct(
        ComentarioRepository $comentarioRepository,
        ReporteRepository $reporteRepository,
        NotificacionRepository $notificacionRepository,
        UsuarioRepository $usuarioRepository
    ) {
        $this->comentarioRepository = $comentarioRepository;
        $this->reporteRepository = $reporteRepository;
        $this->notificacionRepository = $notificacionRepository;
        $this->usuarioRepository = $usuarioRepository;
    }

    public function handle(Command $command): string
    {
        if (!$command instanceof CrearComentarioCommand) {
            throw new DomainException('Comando inválido');
        }

        $idReporte = new Uuid($command->idReporte());
        $idUsuarioEmisor = new Uuid($command->idUsuarioEmisor());

        $reporte = $this->reporteRepository->findById($idReporte);
        if (!$reporte) {
            throw new DomainException('Reporte no encontrado');
        }

        $usuario = $this->usuarioRepository->searchById($idUsuarioEmisor);
        if (!$usuario) {
            throw new DomainException('Usuario no encontrado');
        }

        if (!$reporte->usuarioTienePermiso($idUsuarioEmisor)) {
            throw new DomainException('No autorizado para comentar en este reporte');
        }

        $comentario = Comentario::crear($idReporte, $idUsuarioEmisor, $command->comentario());
        $this->comentarioRepository->save($comentario);

        // Determinar destinatario de la notificación
        $destinatarioId = $reporte->idUsuarioEmisor()->equals($idUsuarioEmisor)
            ? $reporte->idUsuarioDestinatario()
            : $reporte->idUsuarioEmisor();

        if ($destinatarioId) {
            $mensaje = "Nuevo comentario en el reporte: " . substr($command->comentario(), 0, 50) . "...";
            $notificacion = NotificacionReporte::crear($idReporte, $destinatarioId, $mensaje);
            $this->notificacionRepository->saveReporte($notificacion);
        }

        return $comentario->id()->value();
    }
}