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
    try {
        $etapa = EtapaMaquina::fromString($query->getEtapa());
        $maquinas = $this->maquinaRepository->findByEtapaWithComercio($etapa);
        return ['success' => true, 'maquinas' => $maquinas];
    } catch (DomainException $e) {
        return ['success' => false, 'error' => $e->getMessage(), 'maquinas' => []];
    }
}
}