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

final class RegistrarMaquinaHandler implements CommandHandler
{
    private MaquinaRepository $maquinaRepository;
    private UsuarioRepository $usuarioRepository;
    private ComercioRepository $comercioRepository;
    private ComponenteRepository $componenteRepository;

    public function __construct(
        MaquinaRepository $maquinaRepository,
        UsuarioRepository $usuarioRepository,
        ComercioRepository $comercioRepository,
        ComponenteRepository $componenteRepository
    ) {
        $this->maquinaRepository = $maquinaRepository;
        $this->usuarioRepository = $usuarioRepository;
        $this->comercioRepository = $comercioRepository;
        $this->componenteRepository = $componenteRepository;
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
            // Validar que el ID proporcionado sea un técnico ensamblador válido
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
    //$ensambladores[0] es un array
    $primerEnsamblador = $ensambladores[0];
    if (is_array($primerEnsamblador)) {
        $idEnsamblador = new Uuid($primerEnsamblador['id']);
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
    // $comprobadores[0] es un array
    $primerComprobador = $comprobadores[0];
    if (is_array($primerComprobador)) {
        $idComprobador = new Uuid($primerComprobador['id']);
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
$crearNotificacionCommand = new \maquinas_recreativas\Application\Commands\Notificacion\CrearNotificacionMaquinaCommand(
    $command->idUsuarioLogistica(),  // remitente (logística)
    $idEnsamblador->value(),          // destinatario (ensamblador)
    $maquina->id()->value(),          // id máquina
    'Asignación',                      // tipo
    "Se te ha asignado una nueva máquina para ensamblar: {$command->nombre()}"
);

// Obtener el handler de notificaciones desde el contenedor
$notificacionHandler = \Dependencies::get(\maquinas_recreativas\Application\Commands\Notificacion\CrearNotificacionMaquinaHandler::class);
if ($notificacionHandler) {
    $notificacionHandler->handle($crearNotificacionCommand);
    error_log("Notificación creada para ensamblador: {$idEnsamblador->value()}");
}
// Crear notificación para el técnico comprobador
$crearNotificacionComprobadorCommand = new \maquinas_recreativas\Application\Commands\Notificacion\CrearNotificacionMaquinaCommand(
    $command->idUsuarioLogistica(),
    $idComprobador->value(),
    $maquina->id()->value(),
    'Asignación',
    "Se te ha asignado como comprobador para la máquina: {$command->nombre()}"
);

if ($notificacionHandler) {
    $notificacionHandler->handle($crearNotificacionComprobadorCommand);
    error_log("Notificación creada para comprobador: {$idComprobador->value()}");
}
        return $maquina->id()->value();
    }
}