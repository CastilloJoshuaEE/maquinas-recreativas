<?php
// Application/Queries/Maquina/ObtenerMaquinaPorIdHandler.php

namespace maquinas_recreativas\Application\Queries\Maquina;

use maquinas_recreativas\Domain\Maquina\MaquinaRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;

class ObtenerMaquinaPorIdHandler
{
    private MaquinaRepository $maquinaRepository;

    public function __construct(MaquinaRepository $maquinaRepository)
    {
        $this->maquinaRepository = $maquinaRepository;
    }

    public function handle(ObtenerMaquinaPorIdQuery $query): ?array
    {
        $maquina = $this->maquinaRepository->findById(new Uuid($query->getId()));
        if (!$maquina) {
            return null;
        }
        
        $data = $maquina->toArray();
        $comercio = $maquina->getComercio();
        if ($comercio) {
            $data['NombreComercio'] = $comercio->getNombre();
            $data['DireccionComercio'] = $comercio->getDireccion();
        }
        
        return $data;
    }
}