<?php
// application/commands/maquina/RegistrarMaquinaHandler.php

namespace maquinas_recreativas\Application\Commands\Maquina;

use maquinas_recreativas\Application\Commands\Command;
use maquinas_recreativas\Application\Commands\CommandHandler;
use maquinas_recreativas\Domain\Maquina\MaquinaRecreativa;
use maquinas_recreativas\Domain\Maquina\MaquinaRepository;
use maquinas_recreativas\Domain\Usuario\UsuarioRepository;
use maquinas_recreativas\Domain\Comercio\ComercioRepository;
use maquinas_recreativas\Domain\Componente\ComponenteRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;
use maquinas_recreativas\Application\Commands\Notificacion\CrearNotificacionMaquinaHandler;
use maquinas_recreativas\Application\Commands\Notificacion\CrearNotificacionMaquinaCommand;
use maquinas_recreativas\Infrastructure\Security\HistorialHelper;
final class RegistrarMaquinaHandler implements CommandHandler
{
    private MaquinaRepository $maquinaRepository;
    private UsuarioRepository $usuarioRepository;
    private ComercioRepository $comercioRepository;
    private ComponenteRepository $componenteRepository;
    private ?CrearNotificacionMaquinaHandler $notificacionHandler;
    private HistorialHelper $historialHelper; 

    
    public function __construct(
        MaquinaRepository $maquinaRepository,
        UsuarioRepository $usuarioRepository,
        ComercioRepository $comercioRepository,
        ComponenteRepository $componenteRepository,
        ?CrearNotificacionMaquinaHandler $notificacionHandler = null,
        ?HistorialHelper $historialHelper = null
    ) {
        $this->maquinaRepository = $maquinaRepository;
        $this->usuarioRepository = $usuarioRepository;
        $this->comercioRepository = $comercioRepository;
        $this->componenteRepository = $componenteRepository;
        $this->notificacionHandler = $notificacionHandler;
        $this->historialHelper = $historialHelper ?? HistorialHelper::getInstance();
    }

    public function handle(Command $command): string
    {
        if (!$command instanceof RegistrarMaquinaCommand) {
            throw new DomainException('Comando inválido');
        }

        // Validar comercio
        $idComercio = new Uuid($command->idComercio());
        $comercio = $this->comercioRepository->buscarPorId($idComercio->value());
        if (!$comercio) {
            throw new DomainException('Comercio no encontrado');
        }

        // Determinar ensamblador
        $idEnsamblador = null;
        if ($command->idEnsamblador() !== null) {
            $ensamblador = $this->usuarioRepository->findById(new Uuid($command->idEnsamblador()));
            if (!$ensamblador || !$ensamblador->esTecnico() || $ensamblador->getEspecialidad() !== 'Ensamblador') {
                throw new DomainException('El ID de ensamblador proporcionado no es válido');
            }
            $idEnsamblador = new Uuid($command->idEnsamblador());
        } else {
            $ensambladores = $this->usuarioRepository->findTecnicosByEspecialidad('Ensamblador');
            if (empty($ensambladores)) {
                throw new DomainException('No hay técnicos ensambladores disponibles');
            }
            $primerEnsamblador = $ensambladores[0];
            if (is_array($primerEnsamblador)) {
                // Usar 'ID_Usuario' que es lo que devuelve la consulta SQL
                $idEnsamblador = new Uuid($primerEnsamblador['ID_Usuario'] ?? $primerEnsamblador['id'] ?? null);
            } else {
                $idEnsamblador = $primerEnsamblador->getId();
            }
        }

        // Determinar comprobador
        $idComprobador = null;
        if ($command->idComprobador() !== null) {
            $comprobador = $this->usuarioRepository->findById(new Uuid($command->idComprobador()));
            if (!$comprobador || !$comprobador->esTecnico() || $comprobador->getEspecialidad() !== 'Comprobador') {
                throw new DomainException('El ID de comprobador proporcionado no es válido');
            }
            $idComprobador = new Uuid($command->idComprobador());
        } else {
            $comprobadores = $this->usuarioRepository->findTecnicosByEspecialidad('Comprobador');
            if (empty($comprobadores)) {
                throw new DomainException('No hay técnicos comprobadores disponibles');
            }
$primerComprobador = $comprobadores[0];
if (is_array($primerComprobador)) {
    // Usar 'ID_Usuario' que es lo que devuelve la consulta SQL
    $idComprobador = new Uuid($primerComprobador['ID_Usuario'] ?? $primerComprobador['id'] ?? null);
} else {
    $idComprobador = $primerComprobador->getId();
}
        }
        
        error_log("Asignando técnicos - Ensamblador: {$idEnsamblador->value()}, Comprobador: {$idComprobador->value()}");

        $maquina = MaquinaRecreativa::crear(
            $command->nombre(),
            $command->tipo(),
            $idComercio,
            $idEnsamblador,
            $idComprobador
        );

        $this->maquinaRepository->save($maquina);
        try {
            $usuario = $this->usuarioRepository->findById(new Uuid($command->idUsuarioLogistica()));
            if ($usuario) {
                $this->historialHelper->registrar(
                    $maquina->id()->value(),
                    $command->idUsuarioLogistica(),
                    'Logistica',
                    'Registro',
                    "Máquina registrada: {$command->nombre()}"
                );
            } else {
                error_log("Usuario no encontrado para historial: {$command->idUsuarioLogistica()}");
            }
        } catch (\Exception $e) {
            error_log("Error registrando historial: " . $e->getMessage());
        }
        // Crear notificaciones SOLO si el handler fue inyectado
        if ($this->notificacionHandler !== null) {
            try {
                // Notificación para ensamblador
                $crearNotificacionCommand = new CrearNotificacionMaquinaCommand(
                    $command->idUsuarioLogistica(),
                    $idEnsamblador->value(),
                    $maquina->id()->value(),
                    'Nuevo montaje',
                    "Se te ha asignado una nueva máquina para ensamblar: {$command->nombre()}"
                );
                $this->notificacionHandler->handle($crearNotificacionCommand);
                error_log("Notificación creada para ensamblador: {$idEnsamblador->value()}");
                
                // Notificación para comprobador
                $crearNotificacionComprobadorCommand = new CrearNotificacionMaquinaCommand(
                    $command->idUsuarioLogistica(),
                    $idComprobador->value(),
                    $maquina->id()->value(),
                    'Comprobar maquina recreativa',
                    "Se te ha asignado como comprobador para la máquina: {$command->nombre()}"
                );
                $this->notificacionHandler->handle($crearNotificacionComprobadorCommand);
                error_log("Notificación creada para comprobador: {$idComprobador->value()}");
            } catch (\Exception $e) {
                error_log("Error al crear notificaciones: " . $e->getMessage());
                // No fallamos el registro de la máquina si las notificaciones fallan
            }
        } else {
            error_log("Notificaciones desactivadas - no se crearon notificaciones para la máquina");
        }
        return $maquina->id()->value();
    }
}