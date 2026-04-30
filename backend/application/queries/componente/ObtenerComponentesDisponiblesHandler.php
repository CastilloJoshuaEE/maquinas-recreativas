<?php
/**
 * application/queries/componente/ObtenerComponentesDisponiblesHandler.php
 */

namespace maquinas_recreativas\Application\Queries\Componente;

use maquinas_recreativas\Domain\Componente\ComponenteRepository;
use maquinas_recreativas\Domain\Componente\TipoComponente;

final class ObtenerComponentesDisponiblesHandler
{
    private ComponenteRepository $componenteRepository;

    public function __construct(ComponenteRepository $componenteRepository)
    {
        $this->componenteRepository = $componenteRepository;
    }

    public function handle(ObtenerComponentesDisponiblesQuery $query): array
    {
        $tipo = $query->getTipo() !== null ? TipoComponente::fromString($query->getTipo()) : null;
        $componentes = $this->componenteRepository->findDisponibles($tipo);

        // Asegurar que siempre retorne un array
        $resultado = [];
        foreach ($componentes as $componente) {
            $resultado[] = [
                'id' => $componente->id()->value(),
                'ID_Componente' => $componente->id()->value(), // Para compatibilidad
                'tipo' => $componente->tipo()->value(),
                'nombre' => $componente->nombre(),
                'precio' => $componente->precio()
            ];
        }
        
        return $resultado;
    }
}