<?php
/**
 * application/commands/recaudacion/ActualizarRecaudacionHandler.php
 *
 * Manejador del comando ActualizarRecaudacion.
 *
 * @package maquinas_recreativas\Application\Commands\Recaudacion
 */
namespace maquinas_recreativas\Application\Commands\Recaudacion;

use maquinas_recreativas\Application\Commands\Command;
use maquinas_recreativas\Application\Commands\CommandHandler;
use maquinas_recreativas\Domain\Recaudacion\RecaudacionRepository;
use maquinas_recreativas\Domain\Maquina\MaquinaRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;

final class ActualizarRecaudacionHandler implements CommandHandler
{
    private RecaudacionRepository $recaudacionRepository;
    private MaquinaRepository $maquinaRepository;
    
    public function __construct(RecaudacionRepository $recaudacionRepository, MaquinaRepository $maquinaRepository)
    {
        $this->recaudacionRepository = $recaudacionRepository;
        $this->maquinaRepository = $maquinaRepository;
    }
public function handle(Command $command): void
{
    if (!$command instanceof ActualizarRecaudacionCommand) {
        throw new DomainException('Comando inválido');
    }

    $idRecaudacion = new Uuid($command->idRecaudacion());
    $recaudacion = $this->recaudacionRepository->findById($idRecaudacion);
    
    if (!$recaudacion) {
        throw new DomainException('Recaudación no encontrada');
    }
    
    $idMaquina = new Uuid($command->idMaquina());
    $maquina = $this->maquinaRepository->findById($idMaquina);
    
    if (!$maquina) {
        throw new DomainException('Máquina no encontrada');
    }

   error_log("=== ACTUALIZANDO RECAUDACIÓN ===");
    error_log("ID: " . $command->idRecaudacion());
    error_log("Detalle nuevo: " . $command->detalle());
    error_log("Fecha nueva: " . $command->fecha());

    $fecha = !empty($command->fecha()) 
        ? new \DateTimeImmutable($command->fecha())
        : new \DateTimeImmutable();
    
    $recaudacion->actualizar(
        $command->tipoComercio(),
        $command->montoTotal(),
        $command->porcentajeComercio(),
        $command->detalle(),
        $fecha
    );

    error_log("Después de actualizar - detalle en entidad: " . $recaudacion->detalle());
    error_log("Después de actualizar - fecha en entidad: " . $recaudacion->fecha()->format('Y-m-d H:i:s'));

    $this->recaudacionRepository->save($recaudacion);
    error_log("=== SAVE COMPLETADO ===");
}
}