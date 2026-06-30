<?php
/**
 * application/commands/notificacion/MarcarTodasComoLeidasHandler.php
 *
 * Manejador del comando MarcarTodasComoLeidas.
 *
 * @package maquinas_recreativas\Application\Commands\Notificacion
 */

namespace maquinas_recreativas\Application\Commands\Notificacion;

use maquinas_recreativas\Application\Commands\Command;
use maquinas_recreativas\Application\Commands\CommandHandler;
use maquinas_recreativas\Domain\Notificacion\NotificacionRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;

final class MarcarTodasComoLeidasHandler implements CommandHandler
{
    private NotificacionRepository $notificacionRepository;

    public function __construct(NotificacionRepository $notificacionRepository)
    {
        $this->notificacionRepository = $notificacionRepository;
    }

    public function handle(Command $command): void
    {
        if (!$command instanceof MarcarTodasComoLeidasCommand) {
            throw new DomainException('Comando inválido');
        }

        $idUsuario = new Uuid($command->idUsuario());

        $this->notificacionRepository->marcarTodasLeidasReporte($idUsuario);
    }
}