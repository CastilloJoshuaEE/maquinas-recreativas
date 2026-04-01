<?php
/**
 * application/commands/notificacion/MarcarComoLeidaHandler.php
 *
 * Manejador del comando MarcarComoLeida.
 *
 * @package maquinas_recreativas\Application\Commands\Notificacion
 */
namespace maquinas_recreativas\Application\Commands\Notificacion;

use maquinas_recreativas\Application\Commands\Command;
use maquinas_recreativas\Application\Commands\CommandHandler;
use maquinas_recreativas\Domain\Notificacion\NotificacionRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;

final class MarcarComoLeidaHandler implements CommandHandler
{
    private NotificacionRepository $notificacionRepository;

    public function __construct(NotificacionRepository $notificacionRepository)
    {
        $this->notificacionRepository = $notificacionRepository;
    }

    public function handle(Command $command): void
    {
        if (!$command instanceof MarcarComoLeidaCommand) {
            throw new DomainException('Comando inválido');
        }

        $idNotificacion = new Uuid($command->idNotificacion());
        $idUsuario = new Uuid($command->idUsuario());

        $notificacionReporte = $this->notificacionRepository->findReporteById($idNotificacion);
        if ($notificacionReporte) {
            $this->notificacionRepository->marcarLeidaReporte($idNotificacion, $idUsuario);
            return;
        }

        $notificacionMaquina = $this->notificacionRepository->findMaquinaById($idNotificacion);
        if ($notificacionMaquina) {
            $this->notificacionRepository->marcarLeidaMaquina($idNotificacion);
            return;
        }

        throw new DomainException('Notificación no encontrada');
    }
}