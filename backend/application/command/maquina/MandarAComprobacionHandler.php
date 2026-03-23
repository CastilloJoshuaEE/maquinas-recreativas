<?php
/**
 * application/commands/maquina/MandarAComprobacionHandler.php
 *
 * Manejador del comando MandarAComprobacion.
 *
 * @package Reconocimiento\Application\Commands\Maquina
 */

namespace Reconocimiento\Application\Commands\Maquina;

use Reconocimiento\Domain\Maquina\MaquinaRecreativa;
use Reconocimiento\Domain\Maquina\MaquinaRepository;
use Reconocimiento\Domain\Usuario\UsuarioRepository;
use Reconocimiento\Domain\Notificacion\NotificacionMaquina;
use Reconocimiento\Domain\Notificacion\NotificacionRepository;
use Reconocimiento\Domain\Historial\HistorialMaquina;
use Reconocimiento\Domain\Historial\HistorialRepository;
use Reconocimiento\Domain\Shared\ValueObjects\Uuid;
use Reconocimiento\Domain\Shared\Exceptions\DomainException;

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