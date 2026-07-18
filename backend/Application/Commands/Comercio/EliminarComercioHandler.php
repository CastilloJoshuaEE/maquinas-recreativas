<?php

declare(strict_types=1);

namespace maquinas_recreativas\Application\Commands\Comercio;

use maquinas_recreativas\Application\Commands\Command;
use maquinas_recreativas\Application\Commands\CommandHandler;
use maquinas_recreativas\Domain\Comercio\ComercioRepository;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;

/**
 * Manejador para eliminar un comercio
 */
final class EliminarComercioHandler implements CommandHandler
{
    private ComercioRepository $comercioRepository;

    public function __construct(ComercioRepository $comercioRepository)
    {
        $this->comercioRepository = $comercioRepository;
    }

    public function handle(Command $command): void
    {
        if (!$command instanceof EliminarComercioCommand) {
            throw new DomainException('Comando inválido');
        }

        // Verificar que el comercio existe
        $comercio = $this->comercioRepository->buscarPorId($command->getId());
        if (!$comercio) {
            throw new DomainException('Comercio no encontrado');
        }

        // Verificar si tiene máquinas asociadas
        if ($this->comercioRepository->tieneMaquinas($command->getId())) {
            throw new DomainException('No se puede eliminar el comercio porque tiene máquinas asociadas');
        }

        // Eliminar comercio
        $this->comercioRepository->eliminar($command->getId());
    }
}