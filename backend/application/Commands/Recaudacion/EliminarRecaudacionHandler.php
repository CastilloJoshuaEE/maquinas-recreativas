<?php
/**
 * application/commands/recaudacion/EliminarRecaudacionHandler.php
 *
 * Manejador del comando EliminarRecaudacion.
 *
 * @package maquinas_recreativas\Application\Commands\Recaudacion
 */

namespace maquinas_recreativas\Application\Commands\Recaudacion;

use maquinas_recreativas\Application\Commands\Command;
use maquinas_recreativas\Application\Commands\CommandHandler;
use maquinas_recreativas\Domain\Recaudacion\RecaudacionRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;

final class EliminarRecaudacionHandler implements CommandHandler
{
    private RecaudacionRepository $recaudacionRepository;

    public function __construct(RecaudacionRepository $recaudacionRepository)
    {
        $this->recaudacionRepository = $recaudacionRepository;
    }

    public function handle(Command $command): void
    {
        if (!$command instanceof EliminarRecaudacionCommand) {
            throw new DomainException('Comando inválido');
        }

        $idRecaudacion = new Uuid($command->idRecaudacion());
        $recaudacion = $this->recaudacionRepository->findById($idRecaudacion);

        if (!$recaudacion) {
            throw new DomainException('Recaudación no encontrada');
        }

        $this->recaudacionRepository->delete($idRecaudacion);
    }
}