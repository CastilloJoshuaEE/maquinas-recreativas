<?php
/**
 * application/queries/maquina/ObtenerMaquinasPorEstadoHandler.php
 */

namespace maquinas_recreativas\Application\Queries\Maquina;

use maquinas_recreativas\Domain\Maquina\MaquinaRepository;
use maquinas_recreativas\Domain\Maquina\EstadoMaquina;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;

final class ObtenerMaquinasPorEstadoHandler
{
    private MaquinaRepository $maquinaRepository;

    public function __construct(MaquinaRepository $maquinaRepository)
    {
        $this->maquinaRepository = $maquinaRepository;
    }

    public function handle(ObtenerMaquinasPorEstadoQuery $query): array
    {
        $estado = EstadoMaquina::fromString($query->getEstado());
        
        // Usar el método que incluye datos del comercio
        $maquinas = $this->maquinaRepository->findByEstadoWithComercio($estado);
        
        return ['success' => true, 'maquinas' => $maquinas];
    }
}