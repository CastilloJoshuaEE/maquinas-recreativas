<?php
/**
 * application/commands/maquina/DarMantenimientoHandler.php
 *
 * Manejador del comando DarMantenimiento.
 *
 * @package maquinas_recreativas\Application\Commands\Maquina
 */

namespace maquinas_recreativas\Application\Commands\Maquina;

use maquinas_recreativas\Domain\Maquina\MaquinaRecreativa;
use maquinas_recreativas\Domain\Maquina\MaquinaRepository;
use maquinas_recreativas\Domain\Usuario\UsuarioRepository;
use maquinas_recreativas\Domain\Comercio\ComercioRepository;
use maquinas_recreativas\Domain\Notificacion\NotificacionMaquina;
use maquinas_recreativas\Domain\Notificacion\NotificacionRepository;
use maquinas_recreativas\Domain\Distribucion\DistribucionRepository;
use maquinas_recreativas\Domain\Historial\HistorialMaquina;
use maquinas_recreativas\Domain\Historial\HistorialRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;

/**
 * Class DarMantenimientoHandler
 */
final class DarMantenimientoHandler
{
    private MaquinaRepository $maquinaRepository;
    private UsuarioRepository $usuarioRepository;
    private ComercioRepository $comercioRepository;
    private NotificacionRepository $notificacionRepository;
    private DistribucionRepository $distribucionRepository;
    private HistorialRepository $historialRepository;

    public function __construct(
        MaquinaRepository $maquinaRepository,
        UsuarioRepository $usuarioRepository,
        ComercioRepository $comercioRepository,
        NotificacionRepository $notificacionRepository,
        DistribucionRepository $distribucionRepository,
        HistorialRepository $historialRepository
    ) {
        $this->maquinaRepository = $maquinaRepository;
        $this->usuarioRepository = $usuarioRepository;
        $this->comercioRepository = $comercioRepository;
        $this->notificacionRepository = $notificacionRepository;
        $this->distribucionRepository = $distribucionRepository;
        $this->historialRepository = $historialRepository;
    }

    public function handle(DarMantenimiento $command): void
    {
        $idMaquina = new Uuid($command->idMaquina());
        $maquina = $this->maquinaRepository->findById($idMaquina);

        if (!$maquina) {
            throw new DomainException('Máquina no encontrada');
        }

        $idLogistica = new Uuid($command->idLogistica());
        $logistica = $this->usuarioRepository->findById($idLogistica);

        if (!$logistica || !$logistica->esLogistica()) {
            throw new DomainException('Usuario logística no válido');
        }

        $tecnicosMantenimiento = $this->usuarioRepository->findTecnicosByEspecialidad('Mantenimiento');

        if (empty($tecnicosMantenimiento)) {
            throw new DomainException('No hay técnicos de mantenimiento disponibles');
        }

        $tecnico = $tecnicosMantenimiento[0];
        $comercio = $this->comercioRepository->findById($maquina->idComercio());

        $maquina->solicitarMantenimiento($tecnico->id());
        $this->maquinaRepository->save($maquina);

        // Registrar historial
        $historial = HistorialMaquina::registrar(
            $idMaquina,
            $idLogistica,
            $logistica->tipo()->value(),
            'Solicitud de mantenimiento',
            "Máquina enviada a mantenimiento. Técnico asignado: {$tecnico->nombreCompleto()}. Motivo: {$command->mensaje()}",
            'Operativa',
            'No operativa',
            'Recaudacion',
            'Montaje',
            $_SERVER['REMOTE_ADDR'] ?? null,
            ['tecnico_asignado' => $tecnico->nombreCompleto(), 'motivo' => $command->mensaje()]
        );
        $this->historialRepository->save($historial);

        // Crear notificación al técnico
        $nombreComercio = $comercio ? $comercio->nombre() : 'N/A';
        $mensajeCompleto = $command->mensaje() . " - Máquina: {$maquina->nombre()}, Comercio: {$nombreComercio}";

        $notificacion = NotificacionMaquina::crear(
            $idLogistica,
            $tecnico->id(),
            $idMaquina,
            'Dar mantenimiento a máquina recreativa',
            $mensajeCompleto
        );
        $this->notificacionRepository->saveMaquina($notificacion);

        // Actualizar informe de distribución
        $this->distribucionRepository->updateEstado($idMaquina, 'No operativa');
    }
}