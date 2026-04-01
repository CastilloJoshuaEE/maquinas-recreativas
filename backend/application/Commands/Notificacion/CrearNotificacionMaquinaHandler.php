<?php
namespace maquinas_recreativas\Application\Commands\Notificacion;

use maquinas_recreativas\Application\Commands\Command;
use maquinas_recreativas\Application\Commands\CommandHandler;
use maquinas_recreativas\Domain\Notificacion\NotificacionMaquina;
use maquinas_recreativas\Domain\Notificacion\NotificacionRepository;
use maquinas_recreativas\Domain\Usuario\UsuarioRepository;
use maquinas_recreativas\Domain\Maquina\MaquinaRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;

final class CrearNotificacionMaquinaHandler implements CommandHandler
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

    public function handle(Command $command): void
    {
        if (!$command instanceof CrearNotificacionMaquinaCommand) {
            throw new DomainException('Comando inválido');
        }

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