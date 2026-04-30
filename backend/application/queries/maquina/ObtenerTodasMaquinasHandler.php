<?php
// Application/Queries/Maquina/ObtenerTodasMaquinasHandler.php

namespace maquinas_recreativas\Application\Queries\Maquina;

use maquinas_recreativas\Domain\Maquina\MaquinaRepository;

final class ObtenerTodasMaquinasHandler
{
    private MaquinaRepository $maquinaRepository;

    public function __construct(MaquinaRepository $maquinaRepository)
    {
        $this->maquinaRepository = $maquinaRepository;
    }

    public function handle(ObtenerTodasMaquinasQuery $query): array
    {
        return $this->maquinaRepository->findAllWithComercio();
    }
}