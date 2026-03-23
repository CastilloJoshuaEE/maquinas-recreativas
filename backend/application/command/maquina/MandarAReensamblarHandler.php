<?php
/**
 * application/commands/maquina/MandarAReensamblarHandler.php
 *
 * Manejador del comando MandarAReensamblar.
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
 * Class MandarAReensamblarHandler
 */
final class MandarAReensamblarHandler
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

    public function handle(MandarAReensamblar $command): void
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

        $maquina->enviarAReensamblar();
        $this->maquinaRepository->save($maquina);

        // Registrar historial
        $historial = HistorialMaquina::registrar(
            $idMaquina,
            $idRemitente,
            $remitente->tipo()->value(),
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

        // Crear notificación al ensamblador
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