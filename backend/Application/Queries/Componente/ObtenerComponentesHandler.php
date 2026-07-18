<?php
/**
 * application/queries/componente/ObtenerComponentesHandler.php
 *
 * Manejador del query ObtenerComponentes.
 *
 * @package maquinas_recreativas\Application\Queries\Componente
 */

namespace maquinas_recreativas\Application\Queries\Componente;

use maquinas_recreativas\Domain\Componente\ComponenteRepository;
use maquinas_recreativas\Domain\Componente\TipoComponente;

/**
 * Class ObtenerComponentesHandler
 */
final class ObtenerComponentesHandler
{
    private ComponenteRepository $componenteRepository;

    public function __construct(ComponenteRepository $componenteRepository)
    {
        $this->componenteRepository = $componenteRepository;
    }

    /**
     * Maneja el query de obtener componentes.
     *
     * @param ObtenerComponentesQuery $query
     * @return array
     */
    public function handle(ObtenerComponentesQuery $query): array
    {
        $tipo = $query->getTipo() !== null ? TipoComponente::fromString($query->getTipo()) : null;

        $componentes = $this->componenteRepository->findByTipo($tipo, $query->getLimit(), $query->getOffset());
        $total = $this->componenteRepository->countByTipo($tipo);

        return [
            'componentes' => array_map(function ($componente) {
                return [
                    'id' => $componente->id()->value(),
                    'tipo' => $componente->tipo()->value(),
                    'nombre' => $componente->nombre(),
                    'precio' => $componente->precio(),
                    'esta_disponible' => $componente->estaDisponible()
                ];
            }, $componentes),
            'total' => $total
        ];
    }
}