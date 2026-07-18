<?php
/**
 * application/queries/recaudacion/ObtenerRecaudacionesHandler.php
 */

namespace maquinas_recreativas\Application\Queries\Recaudacion;

use maquinas_recreativas\Domain\Recaudacion\RecaudacionRepository;

final class ObtenerRecaudacionesHandler
{
    private RecaudacionRepository $recaudacionRepository;

    public function __construct(RecaudacionRepository $recaudacionRepository)
    {
        $this->recaudacionRepository = $recaudacionRepository;
    }

    public function handle(ObtenerRecaudacionesQuery $query): array
    {
        $filters = [];
        
        if ($query->getFechaInicio()) {
            $filters['fechaInicio'] = $query->getFechaInicio();
        }
        if ($query->getFechaFin()) {
            $filters['fechaFin'] = $query->getFechaFin();
        }
        if ($query->getIdMaquina()) {
            $filters['idMaquina'] = $query->getIdMaquina();
        }
        if ($query->getTipoComercio()) {
            $filters['tipoComercio'] = $query->getTipoComercio();
        }
        
        error_log("Filtros recibidos: " . json_encode($filters));
        
        $recaudaciones = $this->recaudacionRepository->findAll(
            $filters,
            $query->getLimit(),
            $query->getOffset()
        );
        
        $total = count($recaudaciones);
        
        return [
            'recaudaciones' => $recaudaciones,
            'total' => $total
        ];
    }
}