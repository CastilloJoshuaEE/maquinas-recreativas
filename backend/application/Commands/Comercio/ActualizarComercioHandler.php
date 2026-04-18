<?php

declare(strict_types=1);

namespace maquinas_recreativas\Application\Commands\Comercio;

use maquinas_recreativas\Application\Commands\Command;
use maquinas_recreativas\Application\Commands\CommandHandler;
use maquinas_recreativas\Domain\Comercio\ComercioRepository;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;

/**
 * Manejador para actualizar un comercio
 */
final class ActualizarComercioHandler implements CommandHandler
{
    private ComercioRepository $comercioRepository;

    public function __construct(ComercioRepository $comercioRepository)
    {
        $this->comercioRepository = $comercioRepository;
    }

    public function handle(Command $command): void
    {
        if (!$command instanceof ActualizarComercioCommand) {
            throw new DomainException('Comando inválido');
        }

        // Verificar que el comercio existe
        $comercio = $this->comercioRepository->buscarPorId($command->getId());
        if (!$comercio) {
            throw new DomainException('Comercio no encontrado');
        }

        // Verificar que el nuevo nombre no esté en uso por otro comercio
        if ($this->comercioRepository->existePorNombre($command->getNombre(), $command->getId())) {
            throw new DomainException('Ya existe un comercio con ese nombre');
        }

        // Actualizar datos
        $comercio->actualizar(
            $command->getNombre(),
            $command->getTipo(),
            $command->getDireccion(),
            $command->getTelefono()
        );

        // Guardar cambios
        $this->comercioRepository->guardar($comercio);
    }
}