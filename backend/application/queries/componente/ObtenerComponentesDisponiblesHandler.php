<?php
/**
 * application/queries/componente/ObtenerComponentesDisponiblesHandler.php
 *
 * Manejador del query ObtenerComponentesDisponibles.
 *
 * @package maquinas_recreativas\Application\Queries\Componente
 */

namespace maquinas_recreativas\Application\Queries\Componente;

use maquinas_recreativas\Domain\Componente\ComponenteRepository;
use maquinas_recreativas\Domain\Componente\TipoComponente;

/**
 * Class ObtenerComponentesDisponiblesHandler
 */
final class ObtenerComponentesDisponiblesHandler
{
    private ComponenteRepository $componenteRepository;

    public function __construct(ComponenteRepository $componenteRepository)
    {
        $this->componenteRepository = $componenteRepository;
    }

    /**
     * Maneja el query de obtener componentes disponibles.
     *
     * @param ObtenerComponentesDisponiblesQuery $query
     * @return array
     */
    public function handle(ObtenerComponentesDisponiblesQuery $query): array
    {
        $tipo = $query->getTipo() !== null ? TipoComponente::fromString($query->getTipo()) : null;

        $componentes = $this->componenteRepository->findDisponibles($tipo);

        return array_map(function ($componente) {
            return [
                'id' => $componente->id()->value(),
                'tipo' => $componente->tipo()->value(),
                'nombre' => $componente->nombre(),
                'precio' => $componente->precio()
            ];
        }, $componentes);
    }
}