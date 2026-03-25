<?php
/**
 * application/commands/notificacion/MarcarTodasComoLeidasHandler.php
 *
 * Manejador del comando MarcarTodasComoLeidas.
 *
 * @package Reconocimiento\Application\Commands\Notificacion
 */

namespace Reconocimiento\Application\Commands\Notificacion;

use Reconocimiento\Domain\Notificacion\NotificacionRepository;
use Reconocimiento\Domain\Shared\ValueObjects\Uuid;
use Reconocimiento\Domain\Shared\Exceptions\DomainException;

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