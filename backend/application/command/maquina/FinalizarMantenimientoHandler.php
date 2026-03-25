<?php
/**
 * application/commands/maquina/FinalizarMantenimientoHandler.php
 *
 * Manejador del comando FinalizarMantenimiento.
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
 * Class FinalizarMantenimientoHandler
 */
final class FinalizarMantenimientoHandler
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

    public function handle(FinalizarMantenimiento $command): void
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

        $maquina->finalizarMantenimiento($command->exito());
        $this->maquinaRepository->save($maquina);

        // Registrar historial
        $estadoNuevo = $command->exito() ? 'Operativa' : 'Retirada';
        $etapaNueva = $command->exito() ? 'Recaudacion' : 'Distribucion';
        $descripcion = $command->exito()
            ? "Mantenimiento completado con éxito. Máquina operativa. Mensaje: {$command->mensaje()}"
            : "Mantenimiento completado. Máquina retirada. Mensaje: {$command->mensaje()}";

        $historial = HistorialMaquina::registrar(
            $idMaquina,
            $idRemitente,
            $remitente->tipo()->value(),
            'Finalización de mantenimiento',
            $descripcion,
            'No operativa',
            $estadoNuevo,
            'Montaje',
            $etapaNueva,
            $_SERVER['REMOTE_ADDR'] ?? null,
            ['exito' => $command->exito(), 'mensaje' => $command->mensaje()]
        );
        $this->historialRepository->save($historial);

        // Notificar a logísticos
        $logisticos = $this->usuarioRepository->findByTipo('Logistica');
        $tipoNotificacion = $command->exito() ? 'Máquina recreativa reparada' : 'Máquina recreativa retirada';

        foreach ($logisticos as $logistica) {
            $notificacion = NotificacionMaquina::crear(
                $idRemitente,
                $logistica->id(),
                $idMaquina,
                $tipoNotificacion,
                $command->mensaje()
            );
            $this->notificacionRepository->saveMaquina($notificacion);
        }
    }
}