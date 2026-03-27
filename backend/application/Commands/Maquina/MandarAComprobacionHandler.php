<?php
/**
 * application/commands/maquina/MandarAComprobacionHandler.php
 *
 * Manejador del comando MandarAComprobacion.
 *
 * @package maquinas_recreativas\Application\Commands\Maquina
 */

namespace maquinas_recreativas\Application\Commands\Maquina;

use maquinas_recreativas\Domain\Maquina\MaquinaRecreativa;
use maquinas_recreativas\Domain\Maquina\MaquinaRepository;
use maquinas_recreativas\Domain\Usuario\UsuarioRepository;
use maquinas_recreativas\Domain\Notificacion\NotificacionMaquina;
use maquinas_recreativas\Domain\Notificacion\NotificacionRepository;
use maquinas_recreativas\Domain\Historial\HistorialMaquina;
use maquinas_recreativas\Domain\Historial\HistorialRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;

/**
 * Class MandarAComprobacionHandler
 */
final class MandarAComprobacionHandler
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

    public function handle(MandarAComprobacion $command): void
    {
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

        $maquina->enviarAComprobacion();
        $this->maquinaRepository->save($maquina);

        // Registrar historial
        $historial = HistorialMaquina::registrar(
            $idMaquina,
            $idRemitente,
            $remitente->tipo()->value(),
            'Envío a comprobación',
            "Máquina enviada a comprobación. Mensaje: {$command->mensaje()}",
            'Ensamblandose/Reensamblandose',
            'Comprobandose',
            'Montaje',
            'Montaje',
            $_SERVER['REMOTE_ADDR'] ?? null,
            ['mensaje' => $command->mensaje()]
        );
        $this->historialRepository->save($historial);

        // Crear notificación al comprobador
        $notificacion = NotificacionMaquina::crear(
            $idRemitente,
            $maquina->idTecnicoComprobador(),
            $idMaquina,
            'Comprobar máquina recreativa',
            $command->mensaje()
        );
        $this->notificacionRepository->saveMaquina($notificacion);
    }
}