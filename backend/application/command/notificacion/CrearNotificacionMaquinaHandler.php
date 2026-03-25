<?php
/**
 * application/commands/notificacion/CrearNotificacionMaquinaHandler.php
 *
 * Manejador del comando CrearNotificacionMaquina.
 *
 * @package Reconocimiento\Application\Commands\Notificacion
 */

namespace Reconocimiento\Application\Commands\Notificacion;

use Reconocimiento\Domain\Notificacion\NotificacionMaquina;
use Reconocimiento\Domain\Notificacion\NotificacionRepository;
use Reconocimiento\Domain\Usuario\UsuarioRepository;
use Reconocimiento\Domain\Maquina\MaquinaRepository;
use Reconocimiento\Domain\Shared\ValueObjects\Uuid;
use Reconocimiento\Domain\Shared\Exceptions\DomainException;

/**
 * Class CrearNotificacionMaquinaHandler
 */
final class CrearNotificacionMaquinaHandler
{
    private NotificacionRepository $notificacionRepository;
    private UsuarioRepository $usuarioRepository;
    private MaquinaRepository $maquinaRepository;

    public function __construct(
        NotificacionRepository $notificacionRepository,
        UsuarioRepository $usuarioRepository,
        MaquinaRepository $maquinaRepository
    ) {
        $this->notificacionRepository = $notificacionRepository;
        $this->usuarioRepository = $usuarioRepository;
        $this->maquinaRepository = $maquinaRepository;
    }

    public function handle(CrearNotificacionMaquina $command): void
    {
        $idRemitente = new Uuid($command->idRemitente());
        $idDestinatario = new Uuid($command->idDestinatario());
        $idMaquina = new Uuid($command->idMaquina());

        $remitente = $this->usuarioRepository->findById($idRemitente);
        if (!$remitente) {
            throw new DomainException('Usuario remitente no encontrado');
        }

        $destinatario = $this->usuarioRepository->findById($idDestinatario);
        if (!$destinatario) {
            throw new DomainException('Usuario destinatario no encontrado');
        }

        $maquina = $this->maquinaRepository->findById($idMaquina);
        if (!$maquina) {
            throw new DomainException('Máquina no encontrada');
        }

        $notificacion = NotificacionMaquina::crear(
            $idRemitente,
            $idDestinatario,
            $idMaquina,
            $command->tipo(),
            $command->mensaje()
        );

        $this->notificacionRepository->saveMaquina($notificacion);
    }
}