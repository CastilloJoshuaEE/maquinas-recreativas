<?php
/**
 * application/commands/maquina/FinalizarMantenimientoHandler.php
 *
 * Manejador del comando FinalizarMantenimiento.
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