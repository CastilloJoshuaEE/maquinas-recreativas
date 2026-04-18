<?php
/**
 * application/queries/maquina/ObtenerMaquinasPorEtapaHandler.php
 */

namespace maquinas_recreativas\Application\Queries\Maquina;

use maquinas_recreativas\Domain\Maquina\MaquinaRepository;
use maquinas_recreativas\Domain\Maquina\EtapaMaquina;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;

final class ObtenerMaquinasPorEtapaHandler
{
    private MaquinaRepository $maquinaRepository;

    public function __construct(MaquinaRepository $maquinaRepository)
    {
        $this->maquinaRepository = $maquinaRepository;
    }

    public function handle(ObtenerMaquinasPorEtapaQuery $query): array
    {
        $etapa = EtapaMaquina::fromString($query->getEtapa());
        
        // Crear un método en el repositorio que incluya datos del comercio
        $maquinas = $this->maquinaRepository->findByEtapaWithComercio($etapa);
        
        return ['success' => true, 'maquinas' => $maquinas];
    }
}