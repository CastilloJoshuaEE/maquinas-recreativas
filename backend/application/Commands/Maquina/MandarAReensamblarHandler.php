<?php
namespace maquinas_recreativas\Application\Commands\Maquina;

use maquinas_recreativas\Application\Commands\Command;
use maquinas_recreativas\Application\Commands\CommandHandler;
use maquinas_recreativas\Domain\Maquina\MaquinaRepository;
use maquinas_recreativas\Domain\Usuario\UsuarioRepository;
use maquinas_recreativas\Domain\Notificacion\NotificacionMaquina;
use maquinas_recreativas\Domain\Notificacion\NotificacionRepository;
use maquinas_recreativas\Domain\Historial\HistorialMaquina;
use maquinas_recreativas\Domain\Historial\HistorialRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;

final class MandarAReensamblarHandler implements CommandHandler
{
    private MaquinaRepository $maquinaRepository;
    private UsuarioRepository $usuarioRepository;
    private NotificacionRepository $notificacionRepository;
    private HistorialRepository $historialRepository;

    public function __construct(
        MaquinaRepository $maquinaRepository,
        UsuarioRepository $usuarioRepository,
        NotificacionRepository $notificacionRepository,
        HistorialRepository $historialRepository
    ) {
        $this->maquinaRepository = $maquinaRepository;
        $this->usuarioRepository = $usuarioRepository;
        $this->notificacionRepository = $notificacionRepository;
        $this->historialRepository = $historialRepository;
    }

    public function handle(Command $command): void
    {
        if (!$command instanceof MandarAReensamblarCommand) {
            throw new DomainException('Comando inválido');
        }

        $idMaquina = new Uuid($command->idMaquina());
        $maquina = $this->maquinaRepository->findById($idMaquina);

        if (!$maquina) {
            throw new DomainException('Máquina no encontrada');
        }

        $idRemitente = new Uuid($command->idRemitente());
        $remitente = $this->usuarioRepository->findById($idRemitente);

        if (!$remitente) {
            throw new DomainException('Usuario remitente no encontrado');
        }

        $maquina->enviarAReensamblar();
        $this->maquinaRepository->save($maquina);

        $historial = HistorialMaquina::registrar(
            $idMaquina,
            $idRemitente,
            $remitente->getTipo()->value(),
            'Envío a reensamblar',
            "Máquina rechazada, enviada a reensamblar. Motivo: {$command->mensaje()}",
            'Comprobandose',
            'Reensamblandose',
            'Montaje',
            'Montaje',
            $_SERVER['REMOTE_ADDR'] ?? null,
            ['motivo' => $command->mensaje()]
        );
        $this->historialRepository->save($historial);

        $notificacion = NotificacionMaquina::crear(
            $idRemitente,
            $maquina->idTecnicoEnsamblador(),
            $idMaquina,
            'Reensamblar máquina recreativa',
            $command->mensaje()
        );
        $this->notificacionRepository->saveMaquina($notificacion);
    }
}