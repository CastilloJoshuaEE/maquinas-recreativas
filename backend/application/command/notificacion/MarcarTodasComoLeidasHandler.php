<?php
/**
 * application/commands/notificacion/MarcarTodasComoLeidasHandler.php
 *
 * Manejador del comando MarcarTodasComoLeidas.
 *
 * @package maquinas_recreativas\Application\Commands\Notificacion
 */

namespace maquinas_recreativas\Application\Commands\Notificacion;

use maquinas_recreativas\Domain\Notificacion\NotificacionRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;

/**
 * Class MarcarTodasComoLeidasHandler
 */
final class MarcarTodasComoLeidasHandler
{
    private NotificacionRepository $notificacionRepository;

    public function __construct(NotificacionRepository $notificacionRepository)
    {
        $this->notificacionRepository = $notificacionRepository;
    }

    public function handle(MarcarTodasComoLeidas $command): void
    {
        $idUsuario = new Uuid($command->idUsuario());

        $usuario = $this->usuarioRepository->findById($idUsuario);
        if (!$usuario) {
            throw new DomainException('Usuario no encontrado');
        }

        $this->notificacionRepository->marcarTodasLeidasReporte($idUsuario);
    }
}